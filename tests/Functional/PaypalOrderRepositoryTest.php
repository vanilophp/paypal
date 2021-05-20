<?php

declare(strict_types=1);

/**
 * Contains the PaypalOrderRepositoryTest class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-19
 *
 */

namespace Vanilo\Paypal\Tests\Functional;

use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Models\PaypalOrderStatus;
use Vanilo\Paypal\Repository\OrderRepository;
use Vanilo\Paypal\Tests\Dummies\CreatesDummyPayment;
use Vanilo\Paypal\Tests\TestCase;

class PaypalOrderRepositoryTest extends TestCase
{
    use CreatesDummyPayment;

    /** @test */
    public function an_order_can_be_created()
    {
        $repo = $this->getOrderRepository();
        $payment = $this->getPayment('EUR', 9.11);
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
        $payment = $this->getPayment('USD', 15);
        $createdOrder = $repo->create($payment);

        $returnedOrder = $repo->get($createdOrder->id);

        $this->assertInstanceOf(Order::class, $returnedOrder);
        $this->assertEquals($createdOrder->id, $returnedOrder->id);
        $this->assertEquals('USD', $returnedOrder->currency);
        $this->assertEquals(15, $returnedOrder->amount);
        $this->assertEquals($payment->getPaymentId(), $returnedOrder->vaniloPaymentId);
    }

    private function getOrderRepository(): OrderRepository
    {
        return $this->app->make(OrderRepository::class);
    }
}
