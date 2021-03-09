<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Concerns;

trait HasPaypalConfiguration
{
    use HasPaypalCredentials;

    private string $returnUrl;

    private string $cancelUrl;

    private bool $isSandbox;
}
