<?php

declare(strict_types=1);

/**
 * Contains the RequestFactory class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

namespace Vanilo\Paypal\Factories;

use Vanilo\Payment\Contracts\Payment;
use Vanilo\Payment\Support\ReplacesPaymentUrlParameters;
use Vanilo\Paypal\Messages\PaypalPaymentRequest;
use Vanilo\Paypal\Models\Order;
use Vanilo\Paypal\Repository\OrderRepository;

final class RequestFactory
{
    use ReplacesPaymentUrlParameters;

    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function create(Payment $payment, array $options = []): PaypalPaymentRequest
    {
        $paypalOrder = $this->getPaypalOrder(
            $payment,
            $this->url($payment, $options, 'return'),
            $this->url($payment, $options, 'cancel'),
        );
        $result = new PaypalPaymentRequest($paypalOrder->links->approve);

        if (isset($options['view'])) {
            $result->setView($options['view']);
        }

        return $result;
    }

    private function getPaypalOrder(Payment $payment, ?string $returnUrl, ?string $cancelUrl): Order
    {
        if (null !== $payment->remote_id) {
            return $this->orderRepository->get($payment->remote_id);
        }

        $order = $this->orderRepository->create($payment, $returnUrl, $cancelUrl);
        $payment->remote_id = $order->id;
        $payment->save();

        return $order;
    }

    private function url(Payment $payment, array $options, string $which): ?string
    {
        $url = $options["{$which}_url"] ?? null;

        if (null !== $url) {
            $url = $this->replaceUrlParameters($url, $payment);
        }

        return $url;
    }
}
