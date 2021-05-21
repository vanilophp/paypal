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
        [$paypalOrderId, $message] = $this->fetchPaypalOrderId($request);

        $order = $this->orderRepository->get($paypalOrderId);
        $payment = $this->findPayment($order);
        if (null === $payment) {
            throw new PaymentNotFoundException("No matching payment was found for PayPal order `$paypalOrderId`");
        }

        // @todo log `authorized` status/message before capturing
        if ($order->status->isApproved() && $this->autoCapture) {
            $order = $this->orderRepository->capture($order->id);
        }

        $amountPaid = null;
        if ($order->status->isApproved() || $order->status->isCompleted()) {
            /** @todo Take this amount precisely from the payments data */
            $amountPaid = $order->amount;
        }

        return new PaypalPaymentResponse($payment->getPaymentId(), $order->status, $amountPaid, $message);
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

    private function fetchPaypalOrderId(Request $request): array
    {
        if ($request->has('token')) {
            return [$request->get('token'), null];// Frontend
        }

        // Webhook
        return [$request->json('resource.id'), $request->json('summary')];
    }
}
