<?php

declare(strict_types=1);

/**
 * Contains the OrderRepositoryTest class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Tests;

use ReflectionClass;
use Vanilo\Paypal\Client\RealPaypalClient;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalOrderStatus;
use Vanilo\Paypal\Repository\OrderRepository;
use Vanilo\Paypal\Tests\Dummies\CreatesDummyPayment;
use Vanilo\Paypal\Tests\Dummies\InteractsWithFakeOrderRepository;

class OrderRepositoryTest extends TestCase
{
    use CreatesDummyPayment;
    use InteractsWithFakeOrderRepository;

    /** @test */
    public function the_real_paypal_client_gets_injected_by_the_container()
    {
        $repo = app(OrderRepository::class);

        $this->assertInstanceOf(OrderRepository::class, $repo);

        // Look at this Chema!! I'm testing private properties 👻
        $reflector = new ReflectionClass(OrderRepository::class);
        $apiProperty = $reflector->getProperty('client');
        $apiProperty->setAccessible(true);
        $this->assertInstanceOf(RealPaypalClient::class, $apiProperty->getValue($repo));
    }

    /** @test */
    public function an_order_can_be_created()
    {
        $repo = $this->getOrderRepository();
        $payment = $this->createDummyPayment('EUR', 9.11);
        $order = $repo->create($payment);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertIsString($order->id);
        $this->assertNotEmpty($order->id);
        $this->assertEquals('EUR', $order->currency);
        $this->assertEquals(9.11, $order->amount);
        $this->assertTrue($order->status->equals(PaypalOrderStatus::CREATED()));
        $this->assertEquals($payment->getPaymentId(), $order->vaniloPaymentId);
    }

    /** @test */
    public function order_can_be_returned()
    {
        $repo = $this->getOrderRepository();
        $payment = $this->createDummyPayment('USD', 15);
        $createdOrder = $repo->create($payment);

        $returnedOrder = $repo->get($createdOrder->id);

        $this->assertInstanceOf(Order::class, $returnedOrder);
        $this->assertEquals($createdOrder->id, $returnedOrder->id);
        $this->assertEquals('USD', $returnedOrder->currency);
        $this->assertEquals(15, $returnedOrder->amount);
        $this->assertEquals($payment->getPaymentId(), $returnedOrder->vaniloPaymentId);
    }

    /** @test */
    public function the_payments_collection_gets_populated_for_captured_orders()
    {
        $orderRepository = $this->getOrderRepository();
        $payment = $this->createDummyPayment('EUR', 19.35);
        $order = $orderRepository->create($payment);

        $this->fakePaypalClient->simulateOrderApproval($order->id);
        $order = $orderRepository->capture($order->id);

        $this->assertTrue($order->hasPayments());
        $this->assertCount(1, $order->payments());
        $firstPayment = $order->payments()[0];
        $this->assertNotNull($firstPayment->id);
        $this->assertEquals(19.35, $firstPayment->amount);
        $this->assertEquals('EUR', $firstPayment->currency);
        $this->assertTrue($firstPayment->isFinalCapture);
    }
}
