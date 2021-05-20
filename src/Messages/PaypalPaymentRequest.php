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
    private string $view = 'paypal::_request';

    private string $approveUrl;

    public function __construct(string $approveUrl)
    {
        $this->approveUrl = $approveUrl;
    }

    public function getHtmlSnippet(array $options = []): ?string
    {
        return View::make(
            $this->view,
            [
                'url' => $this->approveUrl,
                'autoRedirect' => $options['autoRedirect'] ?? false
            ]
        )->render();
    }

    public function willRedirect(): bool
    {
        return true;
    }

    public function setView(string $view): self
    {
        $this->view = $view;

        return $this;
    }
}
