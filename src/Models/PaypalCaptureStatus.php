<?php

declare(strict_types=1);

/**
 * Contains the PaypalCaptureStatus class.
 *
 * @copyright   Copyright (c) 2025 Lajos Fazakas
 * @author      Lajos Fazakas
 * @license     MIT
 * @since       2025-05-08
 *
 */

namespace Vanilo\Paypal\Models;

use Konekt\Enum\Enum;

/**
 * See: https://developer.paypal.com/docs/api/payments/v2/#captures_get
 *
 * @method static PaypalCaptureStatus COMPLETED()
 * @method static PaypalCaptureStatus DECLINED()
 * @method static PaypalCaptureStatus PENDING()
 * @method static PaypalCaptureStatus FAILED()
 * @method static PaypalCaptureStatus REFUNDED()
 * @method static PaypalCaptureStatus PARTIALLY_REFUNDED()
 *
 * @method bool isCompleted()
 * @method bool isDeclined()
 * @method bool isPending()
 * @method bool isFailed()
 * @method bool isRefunded()
 * @method bool isPartiallyRefunded()
 */
final class PaypalCaptureStatus extends Enum
{
    public const __DEFAULT = self::PENDING;

    public const COMPLETED = 'COMPLETED';
    public const DECLINED = 'DECLINED';
    public const PENDING = 'PENDING';
    public const FAILED = 'FAILED';
    public const REFUNDED = 'REFUNDED';
    public const PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';

    protected static array $labels = [];

    protected static function boot()
    {
        static::$labels = [
            self::COMPLETED => __('The payment was successfully completed.'),
            self::DECLINED => __('The payment was declined.'),
            self::PENDING => __('The payment is pending (e.g. waiting for funds or manual approval).'),
            self::FAILED => __('The payment failed.'),
            self::REFUNDED => __('The payment was refunded.'),
            self::PARTIALLY_REFUNDED => __('Only a portion of the payment was refunded.'),
        ];
    }
}
