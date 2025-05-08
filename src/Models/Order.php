<?php

declare(strict_types=1);

/**
 * Contains the Paypal Order class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Models;

final class Order
{
    public string $id;

    public PaypalOrderStatus $status;

    public PaypalCaptureStatus $captureStatus;

    public Links $links;

    public float $amount;

    public string $currency;

    public ?string $vaniloPaymentId = null;

    private array $payments = [];

    public function __construct(string $id, ?PaypalOrderStatus $status, ?PaypalCaptureStatus $captureStatus, float $amount, string $currency)
    {
        $this->id = $id;
        $this->status = $status ?? new PaypalOrderStatus();
        $this->captureStatus = $captureStatus ?? new PaypalCaptureStatus();
        $this->links = new Links();
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public function hasPayments(): bool
    {
        return !empty($this->payments);
    }

    /** @return Payment[] */
    public function payments(): array
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): void
    {
        $this->payments[] = $payment;
    }
}
