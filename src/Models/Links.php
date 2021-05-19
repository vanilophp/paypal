<?php

declare(strict_types=1);

/**
 * Contains the Paypal Order Links class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Models;

class Links
{
    public ?string $self = null;

    public ?string $approve = null;

    public ?string $update = null;

    public ?string $capture = null;
}
