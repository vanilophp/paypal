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

//    protected static array $labels = [];
//
//    protected static function boot()
//    {
//        static::$labels = [
//            self::CREATED => __('The order was created'),
//            self::SAVED => __('The order is in progress, no purchase was made yet'),
//            self::APPROVED => __('The customer approved the payment through PayPal'),
//            self::VOIDED => __('All purchase units in the order are voided'),
//            self::COMPLETED => __('The payment was authorized or the authorized payment was captured for the order'),
//            self::PAYER_ACTION_REQUIRED => __('The order requires an action from the payer (e.g. 3DS authentication)'),
//        ];
//    }
}
