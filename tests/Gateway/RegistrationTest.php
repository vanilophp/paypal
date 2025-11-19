<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Tests\Gateway;

use PHPUnit\Framework\Attributes\Test;
use Vanilo\Payment\Contracts\PaymentGateway;
use Vanilo\Payment\PaymentGateways;
use Vanilo\Paypal\PaypalPaymentGateway;
use Vanilo\Paypal\Tests\TestCase;

class RegistrationTest extends TestCase
{
    #[Test] public function the_gateway_is_registered_out_of_the_box_with_defaults()
    {
        $this->assertCount(2, PaymentGateways::ids());
        $this->assertContains(PaypalPaymentGateway::DEFAULT_ID, PaymentGateways::ids());
    }

    #[Test] public function the_gateway_can_be_instantiated()
    {
        $payPalGateway = PaymentGateways::make('paypal');

        $this->assertInstanceOf(PaymentGateway::class, $payPalGateway);
        $this->assertInstanceOf(PaypalPaymentGateway::class, $payPalGateway);
    }
}
