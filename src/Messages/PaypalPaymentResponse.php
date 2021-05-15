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

use Konekt\Enum\Enum;
use Vanilo\Payment\Contracts\PaymentResponse;
use Vanilo\Payment\Contracts\PaymentStatus;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class PaypalPaymentResponse implements PaymentResponse
{
    private string $paymentId;

    private ?float $amountPaid;

    private PaypalOrderStatus $nativeStatus;

    public function __construct(string $paymentId, PaypalOrderStatus $nativeStatus, ?float $amountPaid)
    {
        $this->paymentId = $paymentId;
        $this->nativeStatus = $nativeStatus;
        $this->amountPaid = $amountPaid;
    }

    public function wasSuccessful(): bool
    {
        return $this->nativeStatus->equals(PaypalOrderStatus::COMPLETED());
    }

    public function getMessage(): string
    {
        return $this->nativeStatus->label();
    }

    public function getTransactionId(): ?string
    {
        return $this->paymentId;
    }

    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function getStatus(): PaymentStatus
    {
        // TODO: Implement getStatus() method.
    }

    public function getNativeStatus(): Enum
    {
        return $this->nativeStatus;
    }
}
