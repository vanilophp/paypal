<?php

declare(strict_types=1);

use Vanilo\Paypal\PaypalPaymentGateway;

return [
    'gateway' => [
        'register' => true,
        'id' => PaypalPaymentGateway::DEFAULT_ID
    ],
    'bind' => true,
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'secret' => env('PAYPAL_SECRET'),
    'return_url' => env('PAYPAL_RETURN_URL', ''),
    'cancel_url' => env('PAYPAL_CANCEL_URL', ''),
    'sandbox' => (bool) env('PAYPAL_SANDBOX', true),
    'auto_capture_approved_orders' => true,
];
