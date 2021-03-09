<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Models;

use Konekt\Enum\Enum;

final class OrderStatus extends Enum
{
    public const __DEFAULT = self::CREATED;

    public const CREATED = 'CREATED';
    public const SAVED = 'SAVED';
    public const APPROVED = 'APPROVED';
    public const VOIDED = 'VOIDED';
    public const COMPLETED = 'COMPLETED';
    public const PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';
}
