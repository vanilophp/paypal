<?php

declare(strict_types=1);

/**
 * Contains the OrderRepository class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Repository;

use Exception;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpException as PayPalHttpException;
use stdClass;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Paypal\Concerns\InteractsWithPaypalApi;
use Vanilo\Paypal\Exceptions\OrderNotApprovedException;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\Payment as PaypalPayment;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class OrderRepository
{
    use InteractsWithPaypalApi;

    public function create(Payment $payment, string $returnUrl = null, string $cancelUrl = null): Order
    {
        $orderCreateRequest = new OrdersCreateRequest();
        $orderCreateRequest->prefer('return=representation');

        $applicationContext = [];
        if ($returnUrl) {
            $applicationContext['application_context']['return_url'] = $returnUrl;
        }
        if ($cancelUrl) {
            $applicationContext['application_context']['cancel_url'] = $cancelUrl;
        }

        $orderCreateRequest->body = array_merge([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $payment->getCurrency(),
                        'value' => $payment->getAmount()
                    ],
                    'custom_id' => $payment->getPaymentId(),
                ]
            ]
        ], $applicationContext);

        $response = $this->client->execute($orderCreateRequest);

        return $this->orderFromPayload($response->result);
    }

    public function get(string $id): ?Order
    {
        $response = $this->client->execute(new OrdersGetRequest($id));

        if (200 !== $response->statusCode) {
            return null;
        }

        return $this->orderFromPayload($response->result);
    }

    public function capture(string $id): ?Order
    {
        try {
            $request = new OrdersCaptureRequest($id);
            $request->prefer('return=representation');
            $response = $this->client->execute($request);

            if (201 !== $response->statusCode) {
                return null;
            }
        } catch (PayPalHttpException $e) {
            throw $this->convertException($e, $id);
        }

        return $this->orderFromPayload($response->result);
    }

    /**
     * @param stdClass|array|string $payload
     */
    private function orderFromPayload($payload): Order
    {
        // It's highly unlikely that the payload will be string or array but
        // that's what the docblock states in the original paypal SDK and
        // PHP's `json_decode` return type is ambiguous so let's check
        if (!($payload instanceof stdClass)) {
            if (!is_string($payload)) {
                $payload = json_encode($payload);
            }
            $payload = json_decode($payload, false);
        }

        $purchaseUnit = $payload->purchase_units[0];
        $result = new Order(
            $payload->id,
            PaypalOrderStatus::create($payload->status ?? null),
            floatval($purchaseUnit->amount->value),
            $purchaseUnit->amount->currency_code,
        );

        if (property_exists($purchaseUnit, 'custom_id')) {
            $result->vaniloPaymentId = $purchaseUnit->custom_id;
        }

        if (property_exists($payload, 'links')) {
            foreach ($payload->links as $link) {
                if (property_exists($result->links, $link->rel)) {
                    $result->links->{$link->rel} = $link->href;
                }
            }
        }

        if (property_exists($purchaseUnit, 'payments') && property_exists($purchaseUnit->payments, 'captures')) {
            foreach ($purchaseUnit->payments->captures as $capture) {
                $result->addPayment(
                    new PaypalPayment(
                        $capture->id,
                        $capture->status,
                        floatval($capture->amount->value),
                        $capture->amount->currency_code,
                        (bool) $capture->final_capture,
                    )
                );
            }
        }

        return $result;
    }

    private function convertException(PayPalHttpException $e, string $id): Exception
    {
        if (422 == $e->statusCode) {
            $data = json_decode($e->getMessage(), true);
            $details = $data['details'][0] ?? null;
            if (null !== $details && 'ORDER_NOT_APPROVED' === $details['issue']) {
                return new OrderNotApprovedException(
                    $data['message'] ?? 'Order is not approved',
                    ['paypal_order_id' => $id]
                );
            }
        }

        return $e;
    }
}
