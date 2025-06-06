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
use Illuminate\Support\Facades\App;
use Vanilo\Contracts\Address;
use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Contracts\PaymentGateway;
use Vanilo\Payment\Contracts\PaymentRequest;
use Vanilo\Payment\Contracts\PaymentResponse;
use Vanilo\Payment\Contracts\TransactionHandler;
use Vanilo\Paypal\Factories\RequestFactory;
use Vanilo\Paypal\Factories\ResponseFactory;
use Vanilo\Paypal\Transaction\Handler;

class PaypalPaymentGateway implements PaymentGateway
{
    public const DEFAULT_ID = 'paypal';

    private static ?string $svg = null;

    private ?RequestFactory $requestFactory = null;

    private ?ResponseFactory $responseFactory = null;

    public function __construct(readonly string $returnUrl, readonly string $cancelUrl)
    {
    }

    public function createPaymentRequest(Payment $payment, ?Address $shippingAddress = null, array $options = []): PaymentRequest
    {
        if (null === $this->requestFactory) {
            $this->requestFactory = App::make(RequestFactory::class);
        }

        $defaultOptions = [
            'return_url' => $this->returnUrl,
            'cancel_url' => $this->cancelUrl,
        ];

        return $this->requestFactory->create($payment, array_merge($defaultOptions, $options));
    }

    public function processPaymentResponse(Request $request, array $options = []): PaymentResponse
    {
        if (null === $this->responseFactory) {
            $this->responseFactory = App::make(ResponseFactory::class);
        }

        return $this->responseFactory->createFromRequest($request);
    }

    public static function svgIcon(): string
    {
        return self::$svg ??= file_get_contents(__DIR__ . '/resources/logo.svg');
    }

    public function transactionHandler(): ?TransactionHandler
    {
        return new Handler();
    }

    public static function getName(): string
    {
        return 'PayPal';
    }

    public function isOffline(): bool
    {
        return false;
    }
}
