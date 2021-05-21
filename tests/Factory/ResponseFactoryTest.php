<?php

declare(strict_types=1);

/**
 * Contains the ResponseFactoryTest class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-21
 *
 */

namespace Vanilo\Paypal\Tests\Factory;

use Illuminate\Http\Request;
use Vanilo\Paypal\Factories\ResponseFactory;
use Vanilo\Paypal\Models\PaypalOrderStatus;
use Vanilo\Paypal\Tests\Dummies\CreatesDummyPayment;
use Vanilo\Paypal\Tests\Dummies\InteractsWithFakeOrderRepository;
use Vanilo\Paypal\Tests\TestCase;

class ResponseFactoryTest extends TestCase
{
    use CreatesDummyPayment;
    use InteractsWithFakeOrderRepository;

    /** @test */
    public function it_captures_the_payment_if_auto_capture_is_enabled()
    {
        $repo = $this->getOrderRepository();
        $order = $repo->create($this->getPayment('EUR', 29.50));
        $this->fakePaypalClient->simulateOrderApproval($order->id);

        $factory = new ResponseFactory($repo, true);
        $request = new Request(['token' => $order->id]);
        $factory->createFromRequest($request);

        $order = $repo->get($order->id);
        $this->assertEquals(PaypalOrderStatus::COMPLETED, $order->status->value());
    }

    /** @test */
    public function it_does_not_capture_the_payment_if_auto_capture_is_disabled()
    {
        $repo = $this->getOrderRepository();
        $order = $repo->create($this->getPayment('EUR', 29.50));
        $this->fakePaypalClient->simulateOrderApproval($order->id);

        $factory = new ResponseFactory($repo, false);
        $request = new Request(['token' => $order->id]);
        $factory->createFromRequest($request);

        $order = $repo->get($order->id);
        $this->assertEquals(PaypalOrderStatus::APPROVED, $order->status->value());
    }
}
