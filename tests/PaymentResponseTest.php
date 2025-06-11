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

use Vanilo\Payment\Models\PaymentStatus;
use Vanilo\Paypal\Messages\PaypalPaymentResponse;
use Vanilo\Paypal\Models\PaypalCaptureStatus;

class PaymentResponseTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', new PaypalCaptureStatus(), PaymentStatus::PENDING(), 'Yo!', 11.27);

        $this->assertInstanceOf(PaypalPaymentResponse::class, $response);
        $this->assertEquals('XXX.xyz', $response->getPaymentId());
        $this->assertEquals('Yo!', $response->getMessage());
        $this->assertEquals(11.27, $response->getAmountPaid());
    }

    /** @test */
    public function native_status_is_a_paypal_order_status()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', new PaypalCaptureStatus(), PaymentStatus::PENDING(), '');
        $this->assertInstanceOf(PaypalCaptureStatus::class, $response->getNativeStatus());
    }

    /** @test */
    public function native_status_is_set_in_the_constructor()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', PaypalCaptureStatus::PENDING(), PaymentStatus::PENDING(), '');
        $this->assertTrue($response->getNativeStatus()->equals(PaypalCaptureStatus::PENDING()));
    }

    /** @test */
    public function pending_native_status_maps_to_pending_status()
    {
        $response = new PaypalPaymentResponse('', PaypalCaptureStatus::PENDING(), PaymentStatus::PENDING(), '');

        $this->assertTrue($response->getStatus()->isPending());
    }
}
