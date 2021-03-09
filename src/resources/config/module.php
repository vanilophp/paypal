<?php

declare(strict_types=1);

use Vanilo\Paypal\PaypalPaymentGateway;

return [
    'gateway' => [
        'register' => true,
        'id' => PaypalPaymentGateway::DEFAULT_ID
    ],
    'bind' => true,
    'client_id' => env('PAYPAL_CLIENT_ID', 'random-client-id'),
    'secret' => env('PAYPAL_SECRET', 'random-secret'),
    'return_url' => env('PAYPAL_RETURN_URL', 'http://return-url.com'),
    'cancel_url' => env('PAYPAL_CANCEL_URL', 'http://cancel-url.com'),
    'sandbox' => (bool) env('PAYPAL_SANDBOX', false),
];
