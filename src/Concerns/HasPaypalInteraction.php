<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Concerns;

trait HasPaypalInteraction
{
    use HasPaypalConfiguration;

    public function __construct(string $clientId, string $secret, string $returnUrl, string $cancelUrl, bool $isSandbox)
    {
        $this->clientId  = $clientId;
        $this->secret    = $secret;
        $this->returnUrl = $returnUrl;
        $this->cancelUrl = $cancelUrl;
        $this->isSandbox = $isSandbox;
    }
}
