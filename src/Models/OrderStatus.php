<?php

declare(strict_types=1);

/**
 * Contains the OrderStatus class.
 *
 * @copyright   Copyright (c) 2021 Hunor Kedves
 * @author      Hunor Kedves
 * @license     MIT
 * @since       2021-03-09
 *
 */

namespace Vanilo\Paypal\Models;

use Konekt\Enum\Enum;

/**
 * @method static OrderStatus CREATED()
 * @method static OrderStatus SAVED()
 * @method static OrderStatus APPROVED()
 * @method static OrderStatus VOIDED()
 * @method static OrderStatus COMPLETED()
 * @method static OrderStatus PAYER_ACTION_REQUIRED()
 */
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
