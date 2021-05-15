<?php

declare(strict_types=1);

/**
 * Contains the PaypalOrderStatus class.
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
 * @method static PaypalOrderStatus CREATED()
 * @method static PaypalOrderStatus SAVED()
 * @method static PaypalOrderStatus APPROVED()
 * @method static PaypalOrderStatus VOIDED()
 * @method static PaypalOrderStatus COMPLETED()
 * @method static PaypalOrderStatus PAYER_ACTION_REQUIRED()
 *
 * @method bool isCreated()
 * @method bool isSaved()
 * @method bool isApproved()
 * @method bool isVoided()
 * @method bool isCompleted()
 * @method bool isPayerActionRequired()
 */
final class PaypalOrderStatus extends Enum
{
    public const __DEFAULT = self::CREATED;

    public const CREATED = 'CREATED';
    public const SAVED = 'SAVED';
    public const APPROVED = 'APPROVED';
    public const VOIDED = 'VOIDED';
    public const COMPLETED = 'COMPLETED';
    public const PAYER_ACTION_REQUIRED = 'PAYER_ACTION_REQUIRED';
}
