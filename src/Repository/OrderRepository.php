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
use PaypalServerSdkLib\Exceptions\ApiException;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Order as RemoteOrder;
use PaypalServerSdkLib\Models\OrderApplicationContext;
use PaypalServerSdkLib\Models\OrderApplicationContextUserAction;
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
        $applicationContext->setUserAction(OrderApplicationContextUserAction::PAY_NOW);

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

    public function get(string $id): ?Order
    {
       $response = $this->client->getOrder($id);

       if (200 !== $response->getStatusCode()) {
           return null;
       }

       return $this->orderFromPayload($response->getResult());
    }

    public function capture(string $id): ?Order
    {
       try {
           $response = $this->client->captureOrder($id);

           if (201 !== $response->getStatusCode()) {
               return null;
           }
       } catch (ApiException $e) {
           throw $this->convertException($e, $id);
       }

       return $this->orderFromPayload($response->getResult());
    }

    private function orderFromPayload(RemoteOrder $order): Order
    {
        $purchaseUnit = $order->getPurchaseUnits()[0];

        $result = new Order(
            $order->getId(),
            PaypalOrderStatus::create($order->getStatus() ?? null),
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

    // TOREVIEW
    private function convertException(ApiException $e, string $id): Exception
    {
        if (422 == $e->getCode()) {
            $data = json_decode($e->getMessage(), true);
            $details = $data['details'][0] ?? null;
            if (null !== $details && 'ORDER_NOT_APPROVED' === $details['issue']) {
                return new OrderNotApprovedException($data['message'] ?? "Order $id is not approved");
            }
        }

        return $e;
    }
}
