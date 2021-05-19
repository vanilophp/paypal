<?php

declare(strict_types=1);

/**
 * Contains the OrderRepository class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Repository;

use Vanilo\Paypal\Concerns\InteractsWithPaypalApi;

class OrderRepository
{
    use InteractsWithPaypalApi;
}
