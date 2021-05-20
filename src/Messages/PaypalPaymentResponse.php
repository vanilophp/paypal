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
use Vanilo\Paypal\Models\PaypalOrderStatus;

class PaypalPaymentResponse implements PaymentResponse
{
    private string $paymentId;

    private ?float $amountPaid;

    private PaypalOrderStatus $nativeStatus;

    private ?PaymentStatus $status = null;

    public function __construct(string $paymentId, PaypalOrderStatus $nativeStatus, ?float $amountPaid)
    {
        $this->paymentId = $paymentId;
        $this->nativeStatus = $nativeStatus;
        $this->amountPaid = $amountPaid;
    }

    public function wasSuccessful(): bool
    {
        return $this->nativeStatus->isCompleted();
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
        if (null === $this->status) {
            switch ($this->nativeStatus->value()) {
                case PaypalOrderStatus::CREATED:
                case PaypalOrderStatus::SAVED:
                    $this->status = PaymentStatusProxy::PENDING();
                break;

                case PaypalOrderStatus::APPROVED:
                    $this->status = PaymentStatusProxy::AUTHORIZED();
                    break;

                case PaypalOrderStatus::VOIDED:
                    $this->status = PaymentStatusProxy::CANCELLED();
                    break;

                case PaypalOrderStatus::COMPLETED:
                    $this->status = PaymentStatusProxy::PAID();
                    break;

                case PaypalOrderStatus::PAYER_ACTION_REQUIRED:
                    $this->status = PaymentStatusProxy::ON_HOLD();
                    break;
            }
        }

        return $this->status;
    }

    public function getNativeStatus(): Enum
    {
        return $this->nativeStatus;
    }
}
