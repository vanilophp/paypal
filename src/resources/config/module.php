<?php

declare(strict_types=1);

use Vanilo\Paypal\PaypalPaymentGateway;

return [
    'gateway' => [
        'register' => true,
        'id' => PaypalPaymentGateway::DEFAULT_ID
    ],
    'bind' => true,
];
