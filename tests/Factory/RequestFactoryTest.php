<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Tests\Factory;

use Vanilo\Payment\Factories\PaymentFactory;
use Vanilo\Payment\Models\PaymentMethod;
use Vanilo\Paypal\Factories\RequestFactory;
use Vanilo\Paypal\Messages\PaypalPaymentRequest;
use Vanilo\Paypal\PaypalPaymentGateway;
use Vanilo\Paypal\Tests\Dummies\Order;
use Vanilo\Paypal\Tests\TestCase;

class RequestFactoryTest extends TestCase
{
    private PaymentMethod $method;

    protected function setUp(): void
    {
        parent::setUp();

        $this->method = PaymentMethod::create([
            'gateway' => PaypalPaymentGateway::getName(),
            'name' => 'PayPal',
        ]);
    }

    /** @test */
    public function it_creates_a_request_object()
    {
        $factory = new RequestFactory('mid', 'some-key', 'return', 'cancel', true);
        $order = Order::create(['currency' => 'USD', 'amount' => 13.99]);
        $payment = PaymentFactory::createFromPayable($order, $this->method);

        $this->assertInstanceOf(
            PaypalPaymentRequest::class,
            $factory->create($payment)
        );
    }

    /** @test */
    public function it_replaces_parameters_in_the_return_and_cancel_urls()
    {
        $factory = new RequestFactory(
            '123456',
            'secret',
            '/return/{paymentId}',
            '/cancel/{paymentId}',
            true
        );
        $order = Order::create(['currency' => 'EUR', 'amount' => 50]);
        $payment = PaymentFactory::createFromPayable($order, $this->method);

        $request = $factory->create($payment);

        $this->assertEquals(
            'http://localhost/return/' . $payment->getPaymentId(),
            $request->getReturnUrl()
        );

        $this->assertEquals(
            'http://localhost/cancel/' . $payment->getPaymentId(),
            $request->getCancelUrl()
        );
    }

    /** @test */
    public function the_return_and_cancel_urls_can_be_passed_as_an_option_to_the_create_method()
    {
        $factory = new RequestFactory('0XC', 'secret', '', '', true);
        $order = Order::create(['currency' => 'EUR', 'amount' => 90]);
        $payment = PaymentFactory::createFromPayable($order, $this->method);

        $request = $factory->create($payment, [
            'return_url' => '/pp/ret?pid={paymentId}',
            'cancel_url' => '/pp/cancel?pid={paymentId}'
        ]);

        $this->assertEquals(
            'http://localhost/pp/ret?pid=' . $payment->getPaymentId(),
            $request->getReturnUrl()
        );

        $this->assertEquals(
            'http://localhost/pp/cancel?pid=' . $payment->getPaymentId(),
            $request->getCancelUrl()
        );
    }
}
