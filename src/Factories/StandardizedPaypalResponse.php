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

    public function __construct(string $source, string $orderId, ?string $message = null)
    {
        $this->source = $source;
        $this->message = $message;
        $this->orderId = $orderId;
    }

    public static function fromRequest(Request $request): self
    {
        if ($request->has('token')) {
            return new self(self::SOURCE_FRONTEND, $request->get('token'));
        }

        return new self(self::SOURCE_WEBHOOK, $request->json('resource.id'), $request->json('summary'));
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
}
