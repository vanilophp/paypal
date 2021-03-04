<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Factories;

/**
 * Contains the ResponseFactory class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

use Illuminate\Http\Request;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;

final class ResponseFactory
{
    public static function create(): PaypalPaymentResponse
    {
        /** @todo implement the logic */
        return new PaypalPaymentResponse();
    }
}
