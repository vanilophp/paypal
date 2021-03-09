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

use Illuminate\Http\Request;
use Vanilo\Payment\Contracts\PaymentResponse;
use Vanilo\Paypal\Api\PaypalApi;
use Vanilo\Paypal\Concerns\HasPaypalCredentials;
use Vanilo\Paypal\Models\OrderStatus;

class PaypalPaymentResponse implements PaymentResponse
{
    use HasPaypalCredentials;

    private Request $request;

    private string $paymentId;

    private OrderStatus $status;

    private ?float $amountPaid = null;

    public function __construct(Request $request, string $clientId, string $secret, bool $isSandbox)
    {
        $this->request = $request;
        $this->clientId = $clientId;
        $this->secret = $secret;
        $this->isSandbox = $isSandbox;

        $this->capture();
    }

    public function wasSuccessful(): bool
    {
        return $this->status->equals(OrderStatus::COMPLETED());
    }

    public function getMessage(): string
    {
        return $this->status->label();
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

    private function capture(): void
    {
        $token = $this->request->token;
        $captureResponse = (new PaypalApi($this->clientId, $this->secret, $this->isSandbox))->captureOrder($token);
        $this->status = new OrderStatus($captureResponse->result->status);
        $this->paymentId = $token;
        $this->amountPaid = floatval($captureResponse->result->purchase_units[0]->payments->captures[0]->amount->value);
    }
}
