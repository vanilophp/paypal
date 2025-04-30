<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Client;

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

    public function createOrder(OrderRequest $request): ApiResponse
    {
        return $this->client->getOrdersController()->createOrder(['body' => $request, 'prefer' => 'return=representation']);
    }

    public function getOrder($number): ApiResponse
    {
        return $this->client->getOrdersController()->getOrder(['id' => $number, 'prefer' => 'return=representation']);
    }

    public function captureOrder($number): ApiResponse
    {
        return $this->client->getOrdersController()->captureOrder(['id' => $number, 'prefer' => 'return=representation']);
    }
}
