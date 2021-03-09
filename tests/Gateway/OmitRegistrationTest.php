<?php

declare(strict_types=1);

namespace Vanilo\Paypal\Tests\Gateway;

use Vanilo\Payment\PaymentGateways;
use Vanilo\Paypal\Tests\TestCase;

class OmitRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        PaymentGateways::reset();
        parent::setUp();
    }

    /** @test */
    public function the_gateway_registration_can_be_disabled()
    {
        $this->assertCount(1, PaymentGateways::ids());
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        config(['vanilo.paypal.gateway.register' => false]);
    }
}
