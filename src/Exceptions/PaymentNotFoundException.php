<?php

declare(strict_types=1);

/**
 * Contains the PaymentNotFoundException class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-15
 *
 */

namespace Vanilo\Paypal\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentNotFoundException extends NotFoundHttpException
{
}
