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

use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use stdClass;
use Vanilo\Paypal\Concerns\InteractsWithPaypalApi;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class OrderRepository
{
    use InteractsWithPaypalApi;

    public function create(string $currency, float $amount, string $returnUrl = null, string $cancelUrl = null): Order
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
                        'currency_code' => $currency,
                        'value' => $amount
                    ]
                ]
            ]
        ], $applicationContext);

        $response = $this->api->client->execute($orderCreateRequest);

        return $this->orderFromPayload($response->result);
    }

    public function get(string $id): ?Order
    {
        $response = $this->api->client->execute(new OrdersGetRequest($id));

        if (200 !== $response->statusCode) {
            return null;
        }

        return $this->orderFromPayload($response->result);
    }

    public function capture(string $id): ?Order
    {
        $response = $this->api->client->execute(new OrdersCaptureRequest($id));

        if (201 !== $response->statusCode) {
            return null;
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

        $result = new Order($payload->id, PaypalOrderStatus::create($payload->status ?? null));
        if (property_exists($payload, 'links')) {
            foreach ($payload->links as $link) {
                if (property_exists($result->links, $link->rel)) {
                    $result->links->{$link->rel} = $link->href;
                }
            }
        }

        return $result;
    }
}
