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
use Vanilo\Payment\Models\PaymentStatusProxy;
use Vanilo\Paypal\Models\PaypalCaptureStatus;

class PaypalPaymentResponse implements PaymentResponse
{
    public function __construct(
        private string $paymentId,
        private PaypalCaptureStatus $nativeStatus,
        private PaymentStatus $status,
        private string $message,
        private ?float $amountPaid = null,
        private ?string $transactionId = null,
    ) {
    }

    public function wasSuccessful(): bool
    {
        return $this->status->isAnyOf(PaymentStatusProxy::AUTHORIZED(), PaymentStatusProxy::PAID());
    }

    public function getMessage(): string
    {
        return $this->message ?? $this->nativeStatus->label();
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
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
        return $this->status;
    }

    public function getNativeStatus(): Enum
    {
        return $this->nativeStatus;
    }

    public function getTransactionAmount(): float
    {
        return $this->amountPaid ?? 0;
    }
}
