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
use Vanilo\Paypal\Tests\TestCase;

class PaypalOrderRepositoryTest extends TestCase
{
    /** @test */
    public function an_order_can_be_created()
    {
        $repo = $this->getOrderRepository();
        $order = $repo->create('EUR', 9.11);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertIsString($order->id);
        $this->assertNotEmpty($order->id);
        $this->assertTrue($order->status->equals(PaypalOrderStatus::CREATED()));
    }

    /** @test */
    public function order_can_be_returned()
    {
        $repo = $this->getOrderRepository();
        $createdOrder = $repo->create('EUR', 9.11);

        $returnedOrder = $repo->get($createdOrder->id);

        $this->assertInstanceOf(Order::class, $returnedOrder);
        $this->assertEquals($createdOrder->id, $returnedOrder->id);
    }

    private function getOrderRepository(): OrderRepository
    {
        return $this->app->make(OrderRepository::class);
    }
}
