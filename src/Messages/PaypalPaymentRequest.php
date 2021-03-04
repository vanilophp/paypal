<?php

declare(strict_types=1);

/**
 * Contains the PaypalPaymentRequest class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

namespace Vanilo\Paypal\Messages;

use Illuminate\Support\Facades\View;
use Vanilo\Payment\Contracts\PaymentRequest;

class PaypalPaymentRequest implements PaymentRequest
{
    private string $paymentId;

    private string $currency;

    private float $amount;

    private string $view = 'paypal::_request';

    public function getHtmlSnippet(array $options = []): ?string
    {
        return View::make(
            $this->view,
            array_merge(
                $this->encryptData(),
                [
                    'url' => $this->getUrl(),
                    'autoRedirect' => $options['autoRedirect'] ?? false
                ]
            )
        )->render();
    }

    public function willRedirect(): bool
    {
        return true;
    }

    public function setPaymentId(string $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }
}
