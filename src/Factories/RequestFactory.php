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
use Vanilo\Paypal\Concerns\HasPaypalInteraction;
use Vanilo\Paypal\Messages\PaypalPaymentRequest;

final class RequestFactory
{
    use HasPaypalInteraction;

    public function create(Payment $payment, array $options = []): PaypalPaymentRequest
    {
        $result = new PaypalPaymentRequest();

        $result
            ->setPaymentId($payment->getPaymentId())
            ->setCurrency($payment->getCurrency())
            ->setAmount($payment->getAmount())
            ->setClientId($this->clientId)
            ->setSecret($this->secret)
            ->setReturnUrl($this->returnUrl)
            ->setCancelUrl($this->cancelUrl)
            ->setIsSandbox($this->isSandbox);

        if (isset($options['return_url'])) {
            $result->setReturnUrl($options['return_url']);
        }

        if (isset($options['cancel_url'])) {
            $result->setCancelUrl($options['cancel_url']);
        }

        if (isset($options['view'])) {
            $result->setView($options['view']);
        }

        return $result;
    }
}
