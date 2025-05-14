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
use PaypalServerSdkLib\Http\ApiResponse;
use PaypalServerSdkLib\Http\HttpContext;
use PaypalServerSdkLib\Http\HttpRequest;
use PaypalServerSdkLib\Http\HttpResponse;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitBuilder;
use PaypalServerSdkLib\Models\OrderRequest;
use Vanilo\Paypal\Contracts\PaypalClient;
use Vanilo\Paypal\Models\PaypalOrderStatus;
use Vanilo\Support\Generators\NanoIdGenerator;

/**
 * FakePaypalClient for testing purposes
 * emulates responses, stores data in
 * memory for the objects lifetime
 */
class FakePaypalClient implements PaypalClient
{
    private array $data = [];

    private NanoIdGenerator $nanoId;

    public function __construct()
    {
        $this->nanoId = new NanoIdGenerator(17, '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function simulateOrderApproval(string $id): void
    {
        $this->changeOrderStatus($id, PaypalOrderStatus::APPROVED());
    }

    public function createOrder(OrderRequest $request): ApiResponse
    {
        $order = OrderBuilder::init()
            ->id($request->getPurchaseUnits()[0]->getCustomId())
            ->intent('CAPTURE')
            ->status('CREATED')
            ->purchaseUnits([
                PurchaseUnitBuilder::init()
                    ->referenceId($request->getPurchaseUnits()[0]->getCustomId())
                    ->amount(AmountWithBreakdownBuilder::init($request->getPurchaseUnits()[0]->getAmount()->getCurrencyCode(), $request->getPurchaseUnits()[0]->getAmount()->getValue())->build())
                    ->build()
            ])
            ->links([])
            ->build();

        // Build a fake HttpRequest (can be a simple GET to PayPal for this purpose)
        $request = new HttpRequest('POST');

        // Create a fake HttpResponse object with headers, body and status code
        $response = new HttpResponse(
            201,
            ['Content-Type' => ['application/json']],
            json_encode([]) // raw body
        );

        // Construct the HttpContext with the request and response
        $context = new HttpContext(new HttpRequest('POST'), $response);

        $apiResponse = ApiResponse::createFromContext([], $order, $context);

        return $apiResponse;

    }

    public function getOrder($number): ApiResponse
    {
        $order = OrderBuilder::init()
            ->id($number)
            ->intent('CAPTURE')
            ->status('CREATED')
            ->purchaseUnits([
                PurchaseUnitBuilder::init()
                    ->referenceId($number)
                    ->amount(AmountWithBreakdownBuilder::init('EUR', '12')->build())
                    ->build()
            ])
            ->links([])
            ->build();

        // Build a fake HttpRequest (can be a simple GET to PayPal for this purpose)
        $request = new HttpRequest('POST');

        // Create a fake HttpResponse object with headers, body and status code
        $response = new HttpResponse(
            200,
            ['Content-Type' => ['application/json']],
            json_encode([]) // raw body
        );

        // Construct the HttpContext with the request and response
        $context = new HttpContext(new HttpRequest('POST'), $response);

        $apiResponse = ApiResponse::createFromContext([], $order, $context);

        return $apiResponse;
    }

    private function ordersCapture(OrdersCaptureRequest $request): HttpResponse
    {
        // Path looks like: "/v2/checkout/orders/LVDE0MUTB6NX7DP9R/capture?"
        preg_match("/.*\/orders\/([0-9A-Z]*)\/captur.*/", $request->path, $matches);
        $id = $matches[1];

        $this->changeOrderStatus($id, PaypalOrderStatus::COMPLETED());

        $amount = $this->data['orders'][$id]['purchase_units'][0]['amount'];
        $paymentId = $this->nanoId->generate();
        $this->data['orders'][$id]['purchase_units'][0]['payments']['captures'] = [
            [
                'id' => $paymentId,
                'status' => 'COMPLETED',
                'amount' => $amount,
                'final_capture' => true,
                'seller_protection' => [
                    'status' => 'ELIGIBLE',
                    'dispute_categories' => ["ITEM_NOT_RECEIVED", "UNAUTHORIZED_TRANSACTION"]
                ],
                'seller_receivable_breakdown' => [
                    'gross_amount' => $amount,
                    'paypal_fee' => [
                        'currency_code' => $amount['currency_code'],
                        'value' => sprintf('%.2f', floatval($amount['value']) * 0.0054),
                    ],
                    'net_amount' => [
                        'currency_code' => $amount['currency_code'],
                        'value' => sprintf('%.2f', floatval($amount['value']) * 0.9946),
                    ],
                ],
                'custom_id' => $this->data['orders'][$id]['purchase_units'][0]['custom_id'],
                'links' => [
                    [
                        'href' => "https://api.sandbox.paypal.com/v2/payments/captures/$paymentId",
                        'rel' => "self",
                        'method' => "GET"
                    ],
                    [
                        'href' => "https://api.sandbox.paypal.com/v2/payments/captures/$paymentId/refund",
                        'rel' => "refund",
                        'method' => "POST"
                    ],
                    [
                        'href' => "https://api.sandbox.paypal.com/v2/checkout/orders/$id",
                        'rel' => "up",
                        'method' => "GET"
                    ],
                ],
                'create_time' => Carbon::now('UTC')->toIso8601String(),
                'update_time' => Carbon::now('UTC')->toIso8601String(),
            ]
        ];

        return $this->createResponse(201, $this->data['orders'][$id]);
    }

    private function createResponse(int $status, array $body): HttpResponse
    {
        return new HttpResponse($status, json_decode(json_encode($body), false), []);
    }

    private function changeOrderStatus(string $id, PaypalOrderStatus $newStatus): void
    {
        if (isset($this->data['orders'][$id])) {
            $this->data['orders'][$id]['status'] = $newStatus->value();
        }
    }
}
