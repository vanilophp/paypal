<?php

declare(strict_types=1);

/**
 * Contains the PaypalClient interface.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-21
 *
 */

namespace Vanilo\Paypal\Contracts;

use PayPalHttp\HttpRequest;
use PayPalHttp\HttpResponse;

interface PaypalClient
{
    public function execute(HttpRequest $httpRequest): HttpResponse;
}