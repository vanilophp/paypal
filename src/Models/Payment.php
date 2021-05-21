<?php

declare(strict_types=1);

/**
 * Contains the Payment class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-21
 *
 */

namespace Vanilo\Paypal\Models;

final class Payment
{
    public string $id;

    public string $status;

    public float $amount;

    public string $currency;

    public bool $isFinalCapture;

    public function __construct(string $id, string $status, float $amount, string $currency, bool $isFinalCapture)
    {
        $this->id = $id;
        $this->status = $status;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->isFinalCapture = $isFinalCapture;
    }
}
