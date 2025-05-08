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
        // event_type (https://developer.paypal.com/api/rest/webhooks/event-names/#orders)
        //  CHECKOUT.ORDER.APPROVED -> can capture
        //  PAYMENT.CAPTURE.PENDING -> this is what we receive continuosly
        //  PAYMENT.CAPTURE.DECLINED
        //  PAYMENT.CAPTURE.COMPLETED
        //  PAYMENT.CAPTURE.REFUNDED

        // ->capture()
        // returns with status of COMPLETED
        // but the payment.captures[0].status == "PENDING"
        // so in fact we need to wait for "PAYMENT.CAPTURE.COMPLETED" webhook...

        // !!! GET ORDER & CAPTURE
        // if order status == 'COMPLETED' doesn't mean that the payment was captured
        // (it just means that:
        //      - the payment was created
        //      - the intent was completed (but it can be in any of the following states: pending, completed, declined)
        //)
        // we have to look into 'purchase_units[0].payments.captures[0].status' for the proper status...
        // This is also true for the processing of the capture response (which returns also an order)
        //
        // It seems we need to use the order->payments->status
        //
        // ORDER Completed can mean:
        // https://developer.paypal.com/docs/api/orders/v2/
        // "The intent of the order was completed and a payments resource was created.
        // Important: Check the payment status in purchase_units[].payments.captures[].status before fulfilling the order.
        // A completed order can indicate a payment was authorized, an authorized payment was captured,
        // or a payment was declined."

        $standardizedPaypalResponse = StandardizedPaypalResponse::fromRequest($request);

        $paypalOrderId = $standardizedPaypalResponse->orderId();

        $order = $this->orderRepository->get($paypalOrderId);
        $payment = $this->findPayment($order);
        if (null === $payment) {
            throw new PaymentNotFoundException("No matching payment was found for PayPal order `$paypalOrderId`");
        }

        switch ($standardizedPaypalResponse->eventType()) {
            case 'CHECKOUT.ORDER.APPROVED':
                // TODO: capture OR authorize
                return $this->capturePayment($standardizedPaypalResponse, $order, $payment);
                // See: https://developer.paypal.com/api/rest/webhooks/event-names/
            case 'PAYMENT.CAPTURE.COMPLETED':
                $amountPaid = $order->amount;
                $transactionId = $order->payments()[0]->id;
                break;
            case 'PAYMENT.CAPTURE.PENDING':
            case 'PAYMENT.CAPTURE.DENIED':
            case 'PAYMENT.CAPTURE.REFUNDED':
            case 'PAYMENT.CAPTURE.REVERSED':
                $transactionId = null;
                $amountPaid = null;
                break;
        }

        return new PaypalPaymentResponse(
            $payment->getPaymentId(),
            $order->captureStatus,
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

    private function capturePayment(StandardizedPaypalResponse $standardizedPaypalResponse, Order $order, Payment $payment): PaypalPaymentResponse
    {
        $transactionId = null;

        // // @todo log `authorized` status/message before capturing
        // // @todo move it out of here
        if ($order->status->isApproved() && $this->autoCapture) {
            $order = $this->orderRepository->capture($order->id);
        }

        $amountPaid = null;
        if ($order->captureStatus->isCompleted()) {
            /** @todo Take this amount precisely from the payments data */
            $amountPaid = $order->amount;
            $transactionId = $order->payments()[0]->id;
        }

        return new PaypalPaymentResponse(
            $payment->getPaymentId(),
            $order->captureStatus,
            $this->makeResponseMessage($standardizedPaypalResponse, $order),
            $amountPaid,
            $transactionId
        );
    }
}
