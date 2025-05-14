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
use Vanilo\Paypal\Exceptions\PaymentNotFoundException;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalCaptureStatus;
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

        $captureStatus = PaypalCaptureStatus::PENDING();
        $transactionId = null;
        $amountPaid = null;

        // See: https://developer.paypal.com/api/rest/webhooks/event-names/
        switch ($standardizedPaypalResponse->eventType()) {
            case 'CHECKOUT.ORDER.APPROVED':
                // TODO: capture OR authorize
                // We don't process the capture response here, only when the proper webhook arrives
                if ($this->autoCapture) {
                    $this->orderRepository->capture($order->id);
                }
                break;
            case 'CHECKOUT.PAYMENT-APPROVAL.REVERSED':
                $captureStatus = PaypalCaptureStatus::REFUNDED();
                break;
            case 'PAYMENT.CAPTURE.COMPLETED':
                $amountPaid = $order->amount;
                $transactionId = $order->payments()[0]->id;
                $captureStatus = PaypalCaptureStatus::COMPLETED();
                break;
            case 'PAYMENT.CAPTURE.DECLINED':
                $captureStatus = PaypalCaptureStatus::DECLINED();
                break;
            case 'PAYMENT.CAPTURE.PENDING':
                $captureStatus = PaypalCaptureStatus::PENDING();
                break;
            case 'PAYMENT.CAPTURE.REFUNDED':
                $captureStatus = PaypalCaptureStatus::REFUNDED();
                break;
            case 'PAYMENT.CAPTURE.REVERSED':
                $captureStatus = PaypalCaptureStatus::REFUNDED();
                break;
        }

        return new PaypalPaymentResponse(
            $payment->getPaymentId(),
            $captureStatus,
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
