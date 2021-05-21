<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Client;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use Vanilo\Paypal\Contracts\PaypalClient;

class RealPaypalClient implements PaypalClient
{
    private PayPalHttpClient $client;

    public function __construct(string $clientId, string $secret, bool $isSandbox)
    {
        $env = $isSandbox ? new SandboxEnvironment($clientId, $secret) : new ProductionEnvironment($clientId, $secret);

        $this->client = new PayPalHttpClient($env);
    }

    public function execute(HttpRequest $httpRequest): HttpResponse
    {
        return $this->client->execute($httpRequest);
    }
}
