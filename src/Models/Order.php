<?php

declare(strict_types=1);

/**
 * Contains the Paypal Order class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Models;

final class Order
{
    public string $id;

    public PaypalOrderStatus $status;

    public Links $links;

    public function __construct(string $id, ?PaypalOrderStatus $status = null)
    {
        $this->id = $id;
        $this->status = $status ?? new PaypalOrderStatus();
        $this->links = new Links();
    }
}
