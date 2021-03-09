<?php

declare(strict_types=1);

/**
 * Contains the PaypalPaymentGateway class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

namespace Vanilo\Paypal;

use Illuminate\Http\Request;
use Vanilo\Contracts\Address;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Contracts\PaymentGateway;
use Vanilo\Payment\Contracts\PaymentRequest;
use Vanilo\Payment\Contracts\PaymentResponse;
use Vanilo\Paypal\Concerns\HasPaypalInteraction;
use Vanilo\Paypal\Factories\RequestFactory;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;

class PaypalPaymentGateway implements PaymentGateway
{
    use HasPaypalInteraction;

    public const DEFAULT_ID = 'paypal';

    private ?RequestFactory $requestFactory = null;

    public static function getName(): string
    {
        return 'PayPal';
    }

    public function createPaymentRequest(Payment $payment, Address $shippingAddress = null, array $options = []): PaymentRequest
    {
        if (null === $this->requestFactory) {
            $this->requestFactory = new RequestFactory(
                $this->clientId,
                $this->secret,
                $this->returnUrl,
                $this->cancelUrl,
                $this->isSandbox
            );
        }

        return $this->requestFactory->create($payment, $options);
    }

    public function processPaymentResponse(Request $request, array $options = []): PaymentResponse
    {
        return new PaypalPaymentResponse($request, $this->clientId, $this->secret, $this->isSandbox);
    }

    public function isOffline(): bool
    {
        return false;
    }
}
