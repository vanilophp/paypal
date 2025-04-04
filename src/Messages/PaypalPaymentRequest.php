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
use Vanilo\Paypal\Models\Order;

class PaypalPaymentRequest implements PaymentRequest
{
    private string $view = 'paypal::_request';

    public function __construct(readonly Order $order)
    {
    }

    public function getHtmlSnippet(array $options = []): ?string
    {
        return View::make(
            $this->view,
            [
                'url' => $this->order->links->approve,
                'autoRedirect' => $options['autoRedirect'] ?? false,
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

    public function getRemoteId(): ?string
    {
        return $this->order->id;
    }
}
