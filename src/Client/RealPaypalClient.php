<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Client;

use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Http\ApiResponse;
use PaypalServerSdkLib\Models\OrderRequest;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use Vanilo\Paypal\Contracts\PaypalClient;

class RealPaypalClient implements PaypalClient
{
    private PaypalServerSdkClient $client;

    public function __construct(string $clientId, string $secret, bool $isSandbox)
    {
        $this->client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(ClientCredentialsAuthCredentialsBuilder::init($clientId, $secret))
            ->environment($isSandbox ? Environment::SANDBOX : Environment::PRODUCTION)
            //->loggingConfiguration(
            //    LoggingConfigurationBuilder::init()
            //        ->level(LogLevel::INFO)
            //        ->requestConfiguration(RequestLoggingConfigurationBuilder::init()->body(true))
            //        ->responseConfiguration(ResponseLoggingConfigurationBuilder::init()->headers(true))
            //)
            ->build();
    }

    public function execute(OrderRequest $request): ApiResponse
    {
        return $this->client->getOrdersController()->createOrder(['body' => $request, 'prefer' => 'return=representation']);
    }

    public function getOrder($number)
    {
        return $this->client->getOrdersController()->getOrder(['id' => $number]);

    }
}
