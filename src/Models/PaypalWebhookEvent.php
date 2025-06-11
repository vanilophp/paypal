<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Models;

use Konekt\Enum\Enum;

/**
 * @see https://developer.paypal.com/api/rest/webhooks/event-names/#payments
 */
class PaypalWebhookEvent extends Enum
{
    // CHECKOUT/ORDERS ==>
    const CHECKOUT_ORDER_APPROVED = 'CHECKOUT.ORDER.APPROVED'; // A buyer approved a checkout order
    const CHECKOUT_ORDER_COMPLETED = 'CHECKOUT.ORDER.COMPLETED'; // A checkout order is processed. Note: For use by marketplaces and platforms only.
    const CHECKOUT_PAYMENT_APPROVAL_REVERSED = 'CHECKOUT.PAYMENT-APPROVAL.REVERSED'; // A problem occurred after the buyer approved the order but before you captured the payment. Refer to Handle uncaptured payments for what to do when this event occurs.

    // PAYMENTS ==>
    const PAYMENT_AUTHORIZATION_CREATED = 'PAYMENT.AUTHORIZATION.CREATED'; // A payment authorization is created, approved, executed, or a future payment authorization is created.
    const PAYMENT_AUTHORIZATION_VOIDED = 'PAYMENT.AUTHORIZATION.VOIDED'; // A payment authorization is voided either due to authorization reaching it’s 30 day validity period or authorization was manually voided using the Void Authorized Payment API.
    const PAYMENT_CAPTURE_DECLINED = 'PAYMENT.CAPTURE.DECLINED'; // A payment capture is declined.
    const PAYMENT_CAPTURE_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED'; // A payment capture completes.
    const PAYMENT_CAPTURE_PENDING = 'PAYMENT.CAPTURE.PENDING'; // The state of a payment capture changes to pending.
    const PAYMENT_CAPTURE_REFUNDED = 'PAYMENT.CAPTURE.REFUNDED'; // A merchant refunds a payment capture.
    const PAYMENT_CAPTURE_REVERSED = 'PAYMENT.CAPTURE.REVERSED'; // PayPal reverses a payment capture.

    // Payment orders ==>
    const PAYMENT_ORDER_CANCELLED = 'PAYMENT.ORDER.CANCELLED'; // A payment order is canceled. (v1)
    const PAYMENT_ORDER_CREATED = 'PAYMENT.ORDER.CREATED'; //A payment order is created.
}