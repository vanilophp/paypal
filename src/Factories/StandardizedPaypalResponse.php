<?php

declare(strict_types=1);

/**
 * Contains the StandardizedPaypalResponse class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-05-23
 *
 */

namespace Vanilo\Paypal\Factories;

use Illuminate\Http\Request;
use Vanilo\Paypal\Models\PaypalWebhookEvent;

/**
 * This is an internal helper class, to be used by the response factory class to have
 * a unified interface for processing and distinguishing responses received within
 * the context of webhooks and frontend returns, both "return" and "cancel" URL
 */
final class StandardizedPaypalResponse
{
    public const SOURCE_WEBHOOK = 'webhook';
    public const SOURCE_FRONTEND = 'frontend';

    private string $source;

    private ?string $message;

    private string $orderId;

    private ?PaypalWebhookEvent $eventType;

    public function __construct(string $source, string $orderId, null|string|PaypalWebhookEvent $eventType = null, ?string $message = null)
    {
        $this->source = $source;
        $this->message = $message;
        $this->orderId = $orderId;
        $this->eventType = is_string($eventType) ? (PaypalWebhookEvent::has($eventType) ? PaypalWebhookEvent::create($eventType) : null) : $eventType;
    }

    public static function fromRequest(Request $request): self
    {
        if ($request->has('token')) {
            return new self(self::SOURCE_FRONTEND, $request->get('token'));
        }

        $eventType = PaypalWebhookEvent::has($request->json('event_type')) ? PaypalWebhookEvent::create($request->json('event_type')) : null;
        $orderId = self::resolveOrderId($request, $eventType);

        return new self(self::SOURCE_WEBHOOK, $orderId, $eventType, $request->json('summary'));
    }

    public function isWebhook(): bool
    {
        return self::SOURCE_WEBHOOK === $this->source;
    }

    public function isFrontend(): bool
    {
        return self::SOURCE_FRONTEND === $this->source;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function source(): string
    {
        return $this->source;
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function eventType(): ?PaypalWebhookEvent
    {
        return $this->eventType;
    }

    private static function resolveOrderId(Request $request, ?PaypalWebhookEvent $event): string
    {
        switch ($event?->value()) {
            case PaypalWebhookEvent::PAYMENT_CAPTURE_DECLINED:
            case PaypalWebhookEvent::PAYMENT_CAPTURE_COMPLETED:
            case PaypalWebhookEvent::PAYMENT_CAPTURE_PENDING:
            case PaypalWebhookEvent::PAYMENT_CAPTURE_REFUNDED:
            case PaypalWebhookEvent::PAYMENT_CAPTURE_REVERSED:
                return $request->json('resource.supplementary_data.related_ids.order_id');
            case PaypalWebhookEvent::CHECKOUT_PAYMENT_APPROVAL_REVERSED:
                return $request->json('resource.order_id');
            case PaypalWebhookEvent::CHECKOUT_ORDER_APPROVED:
            default:
                return $request->json('resource.id');
        }
    }
}
