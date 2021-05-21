<?php

declare(strict_types=1);

/**
 * Contains the FakePaypalClient class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-21
 *
 */

namespace Vanilo\Paypal\Tests\Fakes;

use Carbon\Carbon;
use Illuminate\Support\Str;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use Vanilo\Paypal\Contracts\PaypalClient;
use Vanilo\Support\Generators\NanoIdGenerator;

/**
 * FakePaypalClient for testing purposes
 * emulates responses, stores data in
 * memory for the objects lifetime
 */
class FakePaypalClient implements PaypalClient
{
    private array $data = [];

    private $observer = null;

    public function __construct()
    {
        $this->nanoId = new NanoIdGenerator(17, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function execute(HttpRequest $httpRequest): HttpResponse
    {
        if (null !== $this->observer) {
            call_user_func($this->observer, $httpRequest);
        }

        $method = $this->getMethodName($httpRequest);

        return method_exists($this, $method) ? $this->{$method}($httpRequest) : $this->unknownRequest($httpRequest);
    }

    public function observeRequestWith(callable $observer): void
    {
        $this->observer = $observer;
    }

    private function ordersCreate(OrdersCreateRequest $request): HttpResponse
    {
        $id = $this->nanoId->generate();
        $body = [
            "id" => $id,
            "status" => "CREATED",
            "links" => [
                [
                    "href" => "https://api-m.paypal.com/v2/checkout/orders/$id",
                    "rel" => "self",
                    "method" => "GET",
                ],
                [
                    "href" => "https://www.paypal.com/checkoutnow?token=$id",
                    "rel" => "approve",
                    "method" => "GET",
                ],
                [
                    "href" => "https://api-m.paypal.com/v2/checkout/orders/$id",
                    "rel" => "update",
                    "method" => "PATCH"
                ],
                [
                    "href" => "https://api-m.paypal.com/v2/checkout/orders/$id/capture",
                    "rel" => "capture",
                    "method" => "POST"
                ]
            ]
        ];

        if ($this->prefersRepresentation($request)) {
            $body = array_merge($body, [
                "intent" => $request->body->intent ?? "CAPTURE",
                "purchase_units" => [
                    [
                        "reference_id" => 'default',
                        "amount" => [
                            "currency_code" => $request->body['purchase_units'][0]['amount']['currency_code'],
                            "value" => $request->body['purchase_units'][0]['amount']['value'],
                        ],
                        "payee" => [
                            "email_address" => 'random@email.neverexisted.here.org',
                            'merchant_id' => 'YYYXXXZZZVB9L'
                        ]
                    ]
                ],
                'create_time' => Carbon::now('UTC')->toIso8601String()
            ]);
            if (isset($request->body['purchase_units'][0]['custom_id'])) {
                $body['purchase_units'][0]['custom_id'] = $request->body['purchase_units'][0]['custom_id'];
            }
        }

        $this->data['orders'][$id] = $body;

        return $this->createResponse(201, $body);
    }

    private function ordersGet(OrdersGetRequest $request): HttpResponse
    {
        $id = Str::replaceLast('?', '', last(explode('/', $request->path)));
        if (!isset($this->data['orders'][$id])) {
            return $this->createResponse(404, []);
        }

        return $this->createResponse(200, $this->data['orders'][$id]);
    }

    private function unknownRequest(HttpRequest $httpRequest): HttpResponse
    {
        throw new \LogicException("Fake Paypal Client: don't know how to handle " . get_class($httpRequest));
    }

    private function getMethodName(HttpRequest $httpRequest): string
    {
        return Str::camel(Str::replaceLast('Request', '', class_basename(get_class($httpRequest))));
    }

    private function createResponse(int $status, array $body): HttpResponse
    {
        return new HttpResponse($status, json_decode(json_encode($body), false), []);
    }

    private function prefersRepresentation(HttpRequest $httpRequest): bool
    {
        $prefers = $httpRequest->headers['Prefer'] ?? '';

        return 'return=representation' === strtolower($prefers);
    }
}
