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
use Illuminate\Support\Facades\Log;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Models\PaymentProxy;
use Vanilo\Paypal\Exceptions\PaymentNotFoundException;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;
use Vanilo\Paypal\Models\Order;
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
        $rawResponse = StandardizedPaypalResponse::fromRequest($request);

        $transactionId = null;
        $paypalOrderId = $rawResponse->orderId();

        $order = $this->orderRepository->get($paypalOrderId);
        $payment = $this->findPayment($order);
        if (null === $payment) {
            throw new PaymentNotFoundException("No matching payment was found for PayPal order `$paypalOrderId`");
        }
        
        // // @todo log `authorized` status/message before capturing
        // // @todo move it out of here
        if ($order->status->isApproved() && $this->autoCapture) {
            $order = $this->orderRepository->capture($order->id);
        }

        $amountPaid = null;
        if ($order->status->isApproved() || $order->status->isCompleted()) {
            /** @todo Take this amount precisely from the payments data */
            $amountPaid = $order->amount;
        }

        if ($order->hasPayments()) {
            $transactionId = $order->payments()[0]->id;
        }

        return new PaypalPaymentResponse(
            $payment->getPaymentId(),
            $order->status,
            $this->makeResponseMessage($rawResponse, $order),
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
