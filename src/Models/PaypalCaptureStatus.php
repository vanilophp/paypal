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
use PaypalServerSdkLib\Models\CaptureStatus;

/**
 * This class turns the SDK's CaptureStatus class into an Enum
 * @see https://developer.paypal.com/docs/api/payments/v2/#captures_get
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

    public const COMPLETED = CaptureStatus::COMPLETED;
    public const DECLINED = CaptureStatus::DECLINED;
    public const PENDING = CaptureStatus::PENDING;
    public const FAILED = CaptureStatus::FAILED;
    public const REFUNDED = CaptureStatus::REFUNDED;
    public const PARTIALLY_REFUNDED = CaptureStatus::PARTIALLY_REFUNDED;

    protected static array $labels = [];

    protected static function boot()
    {
        static::$labels = [
            self::COMPLETED => __('Successfully completed'),
            self::DECLINED => __('Declined'),
            self::PENDING => __('Pending'),
            self::FAILED => __('Failed'),
            self::REFUNDED => __('Refunded'),
            self::PARTIALLY_REFUNDED => __('Partially refunded'),
        ];
    }
}
