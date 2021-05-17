<?php

declare(strict_types=1);

/**
 * Contains the ResponseFactory class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-15
 *
 */

namespace Vanilo\Paypal\Factories;

use Illuminate\Http\Request;
use Vanilo\Paypal\Api\PaypalApi;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;
use Vanilo\Paypal\Models\PaypalOrderStatus;

final class ResponseFactory
{
    private PaypalApi $paypalApi;

    public function __construct(string $clientId, string $secret, bool $isSandbox)
    {
        $this->paypalApi = new PaypalApi($clientId, $secret, $isSandbox);
    }

    public function createFromRequest(Request $request, string $paymentId): PaypalPaymentResponse
    {
        $token = $request->get('token');

        $captureResponse = $this->paypalApi->captureOrder($token);
        $result = $captureResponse->result;
        $status = new PaypalOrderStatus($result['status']);
        $amountPaid = $result['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
        $amountPaid = null !== $amountPaid ? floatval($amountPaid) : $amountPaid;

        return new PaypalPaymentResponse($paymentId, $status, $amountPaid);
    }
}
