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
use Vanilo\Paypal\Models\PaypalCaptureStatus;
use Vanilo\Paypal\Models\PaypalOrderStatus;

class PaymentResponseTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', new PaypalCaptureStatus(), 'Yo!', 11.27);

        $this->assertInstanceOf(PaypalPaymentResponse::class, $response);
        $this->assertEquals('XXX.xyz', $response->getPaymentId());
        $this->assertEquals('Yo!', $response->getMessage());
        $this->assertEquals(11.27, $response->getAmountPaid());
    }

    /** @test */
    public function native_status_is_a_paypal_order_status()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', new PaypalCaptureStatus(), '');
        $this->assertInstanceOf(PaypalCaptureStatus::class, $response->getNativeStatus());
    }

    /** @test */
    public function native_status_is_set_in_the_constructor()
    {
        $response = new PaypalPaymentResponse('XXX.xyz', PaypalCaptureStatus::PENDING(), '');
        $this->assertTrue($response->getNativeStatus()->equals(PaypalCaptureStatus::PENDING()));
    }

    /** @test */
    public function pending_and_failed_native_statuses_map_to_pending_status()
    {
        $response = new PaypalPaymentResponse('', PaypalCaptureStatus::PENDING(), '');

        $this->assertTrue($response->getStatus()->isPending());

        $response = new PaypalPaymentResponse('', PaypalCaptureStatus::FAILED(), '');

        $this->assertTrue($response->getStatus()->isPending());
    }
}
