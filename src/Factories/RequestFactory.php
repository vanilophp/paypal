<?php

declare(strict_types=1);

/**
 * Contains the RequestFactory class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

namespace Vanilo\Paypal\Factories;

use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Support\ReplacesPaymentUrlParameters;
use Vanilo\Paypal\Concerns\HasPaypalInteraction;
use Vanilo\Paypal\Messages\PaypalPaymentRequest;

final class RequestFactory
{
    use HasPaypalInteraction;
    use ReplacesPaymentUrlParameters;

    public function create(Payment $payment, array $options = []): PaypalPaymentRequest
    {
        $result = new PaypalPaymentRequest();

        $result
            ->setPaymentId($payment->getPaymentId())
            ->setCurrency($payment->getCurrency())
            ->setAmount($payment->getAmount())
            ->setClientId($this->clientId)
            ->setSecret($this->secret)
            ->setIsSandbox($this->isSandbox)
            ->setReturnUrl(
                $this->replaceUrlParameters(
                    $options['return_url'] ?? $this->returnUrl,
                    $payment
                )
            )
            ->setCancelUrl(
                $this->replaceUrlParameters(
                    $options['cancel_url'] ?? $this->cancelUrl,
                    $payment
                )
            );

        if (isset($options['view'])) {
            $result->setView($options['view']);
        }

        return $result;
    }
}
