<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Tests\Factory;

use Vanilo\Payment\Factories\PaymentFactory;
use Vanilo\Payment\Models\PaymentMethod;
use Vanilo\Paypal\Factories\RequestFactory;
use Vanilo\Paypal\Messages\PaypalPaymentRequest;
use Vanilo\Paypal\PaypalPaymentGateway;
use Vanilo\Paypal\Tests\Dummies\CreatesDummyPayment;
use Vanilo\Paypal\Tests\Dummies\InteractsWithFakeOrderRepository;
use Vanilo\Paypal\Tests\Dummies\Order;
use Vanilo\Paypal\Tests\TestCase;

class RequestFactoryTest extends TestCase
{
    use CreatesDummyPayment;
    use InteractsWithFakeOrderRepository;

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
        $factory = new RequestFactory($this->getOrderRepository());
        $order = Order::create(['currency' => 'USD', 'amount' => 13.99]);
        $payment = PaymentFactory::createFromPayable($order, $this->method);

        $this->assertInstanceOf(
            PaypalPaymentRequest::class,
            $factory->create($payment)
        );
    }
}
