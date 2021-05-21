<?php

declare(strict_types=1);

/**
 * Contains the InteractsWithFakeOrderRepository trait.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-21
 *
 */

namespace Vanilo\Paypal\Tests\Dummies;

use Vanilo\Paypal\Repository\OrderRepository;
use Vanilo\Paypal\Tests\Fakes\FakePaypalClient;

trait InteractsWithFakeOrderRepository
{
    private ?FakePaypalClient $fakePaypalClient = null;

    private function getOrderRepository(?callable $observer = null): OrderRepository
    {
        $this->fakePaypalClient = new FakePaypalClient();
        if (null !== $observer) {
            $this->fakePaypalClient->observeRequestWith($observer);
        }

        return new OrderRepository($this->fakePaypalClient);
    }
}
