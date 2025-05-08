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
use PaypalServerSdkLib\Models\Builders\PaymentSourceBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletBuilder;
use PaypalServerSdkLib\Models\Builders\PaypalWalletExperienceContextBuilder;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Order as RemoteOrder;
use PaypalServerSdkLib\Models\PaypalExperienceUserAction;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Paypal\Contracts\PaypalClient;
use Vanilo\Paypal\Exceptions\OrderNotApprovedException;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalCaptureStatus;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class OrderRepository
{
    public function __construct(readonly PaypalClient $client)
    {
    }

    public function create(Payment $payment, ?string $returnUrl = null, string $cancelUrl = null): Order
    {
        $paymentSource = PaymentSourceBuilder::init()
            ->paypal(
                PaypalWalletBuilder::init()
                    ->experienceContext(
                        PaypalWalletExperienceContextBuilder::init()
                            ->paymentMethodPreference('IMMEDIATE_PAYMENT_REQUIRED')
                            ->returnUrl($returnUrl)
                            ->cancelUrl($cancelUrl)
                            ->userAction(PaypalExperienceUserAction::PAY_NOW)
                            ->build()
                    )->build()
            )->build();

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
        )
            ->paymentSource($paymentSource)
            ->build();

        $response = $this->client->createOrder($orderCreateRequest);

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

        // Here we suppose a single payment only!!!
        $captureStatus = null;
        if ($captures = $purchaseUnit->getPayments()?->getCaptures()) {
            if ($captures) {
                $capture = $captures[0];
                $captureStatus = $capture->getStatus();
            }
        }

        $result = new Order(
            $order->getId(),
            PaypalOrderStatus::create($order->getStatus() ?? null),
            PaypalCaptureStatus::create($captureStatus),
            floatval($purchaseUnit->getAmount()->getValue()),
            $purchaseUnit->getAmount()->getCurrencyCode(),
        );

        $result->vaniloPaymentId = $purchaseUnit->getCustomId();

        foreach ($order->getLinks() as $link) {
            $result->links->{$link->getRel()} = $link->getHref();
        }

        if ($captures) {
            foreach ($captures as $capture) {
                $result->addPayment(
                    new \Vanilo\Paypal\Models\Payment(
                        $capture->getId(),
                        PaypalCaptureStatus::create($capture->getStatus() ?? null),
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
