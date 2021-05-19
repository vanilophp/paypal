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
use Vanilo\Paypal\Api\PaypalApi;
use Vanilo\Paypal\Repository\OrderRepository;

class OrderRepositoryTest extends TestCase
{
    /** @test */
    public function the_paypal_api_class_gets_injected_by_the_container()
    {
        $repo = app(OrderRepository::class);

        $this->assertInstanceOf(OrderRepository::class, $repo);

        // Look at this Chema!! I'm testing private properties 👻
        $reflector = new ReflectionClass(OrderRepository::class);
        $apiProperty = $reflector->getProperty('api');
        $apiProperty->setAccessible(true);
        $this->assertInstanceOf(PaypalApi::class, $apiProperty->getValue($repo));
    }
}
