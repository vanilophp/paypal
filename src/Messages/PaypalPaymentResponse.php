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
    private string $paymentId;

    private ?float $amountPaid;

    private PaypalCaptureStatus $nativeStatus;

    private ?PaymentStatus $status = null;

    private string $message;

    private ?string $transactionId;

    public function __construct(
        string $paymentId,
        PaypalCaptureStatus $nativeStatus,
        string $message,
        ?float $amountPaid = null,
        ?string $transactionId = null
    ) {
        $this->paymentId = $paymentId;
        $this->nativeStatus = $nativeStatus;
        $this->amountPaid = $amountPaid;
        $this->message = $message;
        $this->transactionId = $transactionId;
    }

    public function wasSuccessful(): bool
    {
        return $this->nativeStatus->isCompleted();
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
        if (null === $this->status) {
            switch ($this->nativeStatus->value()) {
                case PaypalCaptureStatus::COMPLETED:
                    $this->status = PaymentStatusProxy::PAID();
                    break;
                case PaypalCaptureStatus::DECLINED:
                    $this->status = PaymentStatusProxy::DECLINED();
                    break;
                case PaypalCaptureStatus::PENDING:
                case PaypalCaptureStatus::FAILED:
                    $this->status = PaymentStatusProxy::PENDING();
                    break;
                case PaypalCaptureStatus::REFUNDED:
                    $this->status = PaymentStatusProxy::REFUNDED();
                    break;
                case PaypalCaptureStatus::PARTIALLY_REFUNDED:
                    $this->status = PaymentStatusProxy::PARTIALLY_REFUNDED();
                    break;
            }
        }

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
