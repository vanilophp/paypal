<?php

declare(strict_types=1);

/**
 * Contains the InteractsWithPaypalApi trait.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Concerns;

use Vanilo\Paypal\Contracts\PaypalClient;

trait InteractsWithPaypalApi
{
    private PaypalClient $client;

    public function __construct(PaypalClient $client)
    {
        $this->client = $client;
    }
}
