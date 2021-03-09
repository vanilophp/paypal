<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Concerns;

trait HasPaypalCredentials
{
    private string $clientId;

    private string $secret;

    private bool $isSandbox;
}
