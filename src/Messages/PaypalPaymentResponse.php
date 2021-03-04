<?php

declare(strict_types=1);

/**
 * Contains the PaypalPaymentResponse class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

namespace Vanilo\Paypal\Messages;

use Vanilo\Payment\Contracts\PaymentResponse;

class PaypalPaymentResponse implements PaymentResponse
{
    private string $message;

    private string $paymentId;

    public function __construct()
    {
        /** @todo initialize attributes */
    }

    public function wasSuccessful(): bool
    {
        /** @todo implement */
        return true;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getTransactionId(): ?string
    {
        return $this->paymentId;
    }

    public function getAmountPaid(): ?float
    {
        /** @todo implement */
        return 0.00;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }
}
