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

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function createFromRequest(Request $request): PaypalPaymentResponse
    {
        $paypalOrderId = $request->get('token');

        $paypalOrder = $this->orderRepository->get($paypalOrderId);
        $payment = $this->findPayment($paypalOrder);
        if (null === $payment) {
            throw new PaymentNotFoundException("No matching payment was found for PayPal order `$paypalOrderId`");
        }

        $amountPaid = null;
        if ($paypalOrder->status->isApproved() || $paypalOrder->status->isCompleted()) {
            $amountPaid = $paypalOrder->amount;
        }

        return new PaypalPaymentResponse($payment->getPaymentId(), $paypalOrder->status, $amountPaid);
    }

    private function findPayment(Order $paypalOrder): ?Payment
    {
        if (null !== $paypalOrder->vaniloPaymentId) {
            if ($payment = PaymentProxy::find($paypalOrder->vaniloPaymentId)) {
                return $payment;
            }
        }

        // Fallback to locating by Paypal id, as a second chance
        return PaymentProxy::findByRemoteId($paypalOrder->id);
    }
}
