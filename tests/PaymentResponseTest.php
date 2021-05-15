<?php

declare(strict_types=1);

/**
 * Contains the PaymentResponseTest class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-15
 *
 */

namespace Vanilo\Paypal\Tests;

use Vanilo\Paypal\Messages\PaypalPaymentResponse;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class PaymentResponseTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', new PaypalOrderStatus(), 11.27);

        $this->assertInstanceOf(PaypalPaymentResponse::class, $response);
        $this->assertEquals('XXX.xyz', $response->getPaymentId());
        $this->assertEquals(11.27, $response->getAmountPaid());
    }

    /** @test */
    public function native_status_is_a_paypal_order_status()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', new PaypalOrderStatus(), 11.27);
        $this->assertInstanceOf(PaypalOrderStatus::class, $response->getNativeStatus());
    }

    /** @test */
    public function native_status_is_set_in_the_constructor()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', PaypalOrderStatus::PAYER_ACTION_REQUIRED(), 11.27);
        $this->assertTrue($response->getNativeStatus()->equals(PaypalOrderStatus::PAYER_ACTION_REQUIRED()));
    }
}
