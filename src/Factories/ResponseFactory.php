<?php

declare(strict_types=1);

/**
 * Contains the ResponseFactory class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-15
 *
 */

namespace Vanilo\Paypal\Factories;

use Illuminate\Http\Request;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Models\PaymentProxy;
use Vanilo\Payment\Models\PaymentStatusProxy;
use Vanilo\Paypal\Exceptions\PaymentNotFoundException;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalCaptureStatus;
use Vanilo\Paypal\Models\PaypalWebhookEvent;
use Vanilo\Paypal\Repository\OrderRepository;

final class ResponseFactory
{
    private OrderRepository $orderRepository;

    private bool $autoCapture;

    public function __construct(OrderRepository $orderRepository, bool $autoCapture = true)
    {
        $this->orderRepository = $orderRepository;
        $this->autoCapture = $autoCapture;
    }

    public function createFromRequest(Request $request): PaypalPaymentResponse
    {
        $standardizedPaypalResponse = StandardizedPaypalResponse::fromRequest($request);

        $paypalOrderId = $standardizedPaypalResponse->orderId();

        $order = $this->orderRepository->get($paypalOrderId);
        $payment = $this->findPayment($order);
        if (null === $payment) {
            throw new PaymentNotFoundException("No matching payment was found for PayPal order `$paypalOrderId`");
        }

        $rawCaptureStatus = $request->json('resource.status');
        $captureStatus = PaypalCaptureStatus::has($rawCaptureStatus) ? PaypalCaptureStatus::create($rawCaptureStatus) : PaypalCaptureStatus::PENDING();
        $vaniloStatus = match ($captureStatus->value()) {
            PaypalCaptureStatus::PENDING => PaymentStatusProxy::PENDING(),
            PaypalCaptureStatus::FAILED, PaypalCaptureStatus::DECLINED => PaymentStatusProxy::DECLINED(),
            PaypalCaptureStatus::REFUNDED => PaymentStatusProxy::REFUNDED(),
            PaypalCaptureStatus::PARTIALLY_REFUNDED => PaymentStatusProxy::PARTIALLY_REFUNDED(),
            PaypalCaptureStatus::COMPLETED => PaymentStatusProxy::PAID(),
        };

        $transactionId = null;
        $amountPaid = null;

        switch ($standardizedPaypalResponse->eventType()?->value()) {
            case PaypalWebhookEvent::CHECKOUT_ORDER_APPROVED:
                $vaniloStatus = PaymentStatusProxy::AUTHORIZED();

                if (is_array($purchaseUnits = $request->json('resource.purchase_units'))) {
                    $amountPaid = 0;
                    foreach ($purchaseUnits as $purchaseUnit) {
                        $amountPaid += floatval($purchaseUnit['amount']['value']);
                    }
                } else {
                    $amountPaid = $order->amount;
                }
                $transactionId = $request->json('id');
                if ($this->autoCapture) {
                    $this->orderRepository->capture($order->id);
                }
                break;
            case PaypalWebhookEvent::CHECKOUT_PAYMENT_APPROVAL_REVERSED:
            case PaypalWebhookEvent::PAYMENT_CAPTURE_REFUNDED:
            case PaypalWebhookEvent::PAYMENT_CAPTURE_REVERSED:
                // @todo check if the refund is partial and use PARTIALLY_REFUNDED instead
                if ($vaniloStatus->isNoneOf(PaymentStatusProxy::REFUNDED(), PaymentStatusProxy::PARTIALLY_REFUNDED())) {
                    $vaniloStatus = PaymentStatusProxy::REFUNDED();
                }
                break;
            case PaypalWebhookEvent::PAYMENT_CAPTURE_COMPLETED:
                // @todo check the amount and set partial if needed
                $amountPaid = $order->amount;
                $transactionId = $order->payments()[0]->id;
                $captureStatus = PaypalCaptureStatus::COMPLETED();
                break;
            case PaypalWebhookEvent::PAYMENT_CAPTURE_DECLINED:
                $captureStatus = PaypalCaptureStatus::DECLINED();
                break;
        }

        return new PaypalPaymentResponse(
            $payment->getPaymentId(),
            $captureStatus,
            $vaniloStatus,
            $this->makeResponseMessage($standardizedPaypalResponse, $order),
            $amountPaid,
            $transactionId
        );
    }

    private function findPayment(Order $paypalOrder): ?Payment
    {
        if (null !== $paypalOrder->vaniloPaymentId) {
            if ($payment = PaymentProxy::findByHash($paypalOrder->vaniloPaymentId)) {
                return $payment;
            }
        }

        // Fallback to locating by Paypal id, as a second chance
        return PaymentProxy::findByRemoteId($paypalOrder->id);
    }

    private function makeResponseMessage(StandardizedPaypalResponse $paypalResponse, Order $order): string
    {
        return sprintf(
            '%s: %s',
            ucfirst(strtolower($paypalResponse->source())),
            $paypalResponse->message() ?? $order->status->label()
        );
    }
}
