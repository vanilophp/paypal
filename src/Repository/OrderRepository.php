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
use PayPalHttp\HttpException as PayPalHttpException;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Order as RemoteOrder;
use PaypalServerSdkLib\Models\OrderApplicationContext;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Paypal\Contracts\PaypalClient;
use Vanilo\Paypal\Exceptions\OrderNotApprovedException;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class OrderRepository
{
    public function __construct(readonly PaypalClient $client)
    {
    }

    public function create(Payment $payment, ?string $returnUrl = null, string $cancelUrl = null): Order
    {
        $orderCreateRequest = OrderRequestBuilder::init(
            CheckoutPaymentIntent::CAPTURE,
            [
                PurchaseUnitRequestBuilder::init(
                    AmountWithBreakdownBuilder::init(
                        $payment->getCurrency(),
                        (string) $payment->getAmount()
                    )->build()
                )->customId($payment->getPaymentId())->build(),
            ]
        )->build();

        $applicationContext = new OrderApplicationContext();

        if ($returnUrl) {
            $applicationContext->setReturnUrl($returnUrl);
        }
        if ($cancelUrl) {
            $applicationContext->setCancelUrl($cancelUrl);
        }

        $orderCreateRequest->setApplicationContext($applicationContext);

        $response = $this->client->execute($orderCreateRequest);

        return $this->orderFromPayload($response->getResult());
    }

    //public function get(string $id): ?Order
    //{
    //    $response = $this->client->execute(new OrdersGetRequest($id));
    //
    //    if (200 !== $response->statusCode) {
    //        return null;
    //    }
    //
    //    return $this->orderFromPayload($response->result);
    //}
    //
    //public function capture(string $id): ?Order
    //{
    //    try {
    //        $request = new OrdersCaptureRequest($id);
    //        $request->prefer('return=representation');
    //        $response = $this->client->execute($request);
    //
    //        if (201 !== $response->statusCode) {
    //            return null;
    //        }
    //    } catch (PayPalHttpException $e) {
    //        throw $this->convertException($e, $id);
    //    }
    //
    //    return $this->orderFromPayload($response->result);
    //}

    private function orderFromPayload(RemoteOrder $order): Order
    {
        $purchaseUnit = $order->getPurchaseUnits()[0];

        $result = new Order(
            $order->getId(),
            PaypalOrderStatus::create($payload->status ?? null),
            floatval($purchaseUnit->getAmount()->getValue()),
            $purchaseUnit->getAmount()->getCurrencyCode(),
        );

        $result->vaniloPaymentId = $purchaseUnit->getCustomId();

        foreach ($order->getLinks() as $link) {
            $result->links->{$link->getRel()} = $link->getHref();
        }

        if ($captures = $purchaseUnit->getPayments()?->getCaptures()) {
            foreach ($captures as $capture) {
                $result->addPayment(
                    new \Vanilo\Paypal\Models\Payment(
                        $capture->getId(),
                        $capture->getStatus(),
                        floatval($capture->getAmount()->getValue()),
                        $capture->getAmount()->getCurrencyCode(),
                        (bool) $capture->getFinalCapture(),
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
