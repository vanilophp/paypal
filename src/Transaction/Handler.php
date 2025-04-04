<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Transaction;

use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Contracts\PaymentRequest;
use Vanilo\Payment\Contracts\Transaction;
use Vanilo\Payment\Contracts\TransactionHandler;
use Vanilo\Payment\Contracts\TransactionNotCreated;

class Handler implements TransactionHandler
{

    public function supportsRefunds(): bool
    {
        return false;
    }

    public function supportsRetry(): bool
    {
        return false;
    }

    public function allowsRefund(Payment $payment): bool
    {
        return false;
    }

    public function issueRefund(Payment $payment, float $amount, array $options = []): Transaction|TransactionNotCreated
    {
    }

    public function canBeRetried(Payment $payment): bool
    {
        return false;
    }

    public function getRetryRequest(Payment $payment, array $options = []): PaymentRequest|TransactionNotCreated
    {
        // TODO: Implement getRetryRequest() method.
    }
}
