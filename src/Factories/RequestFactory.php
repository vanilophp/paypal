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
use Vanilo\Paypal\Messages\PaypalPaymentRequest;

final class RequestFactory
{
    public function create(Payment $payment, array $options = []): PaypalPaymentRequest
    {
        $result = new PaypalPaymentRequest();
        $billPayer = $payment->getPayable()->getBillPayer();

        $result
            ->setPaymentId($payment->getPaymentId())
            ->setCurrency($payment->getCurrency())
            ->setAmount($payment->getAmount());

        if (isset($options['view'])) {
            $result->setView($options['view']);
        }

        return $result;
    }
}
