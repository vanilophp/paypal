<?php

declare(strict_types=1);

/**
 * Contains the ModuleServiceProvider class.
 *
 * @copyright   Copyright (c) 2021 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2021-03-04
 *
 */

namespace Vanilo\Paypal\Providers;

use Konekt\Concord\BaseModuleServiceProvider;
use Vanilo\Payment\PaymentGateways;
use Vanilo\Paypal\Client\RealPaypalClient;
use Vanilo\Paypal\Contracts\PaypalClient;
use Vanilo\Paypal\PaypalPaymentGateway;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton(PaypalClient::class, function ($app) {
            return new RealPaypalClient(
                $this->config('client_id'),
                $this->config('secret'),
                $this->config('sandbox'),
            );
        });
    }

    public function boot()
    {
        parent::boot();

        if ($this->config('gateway.register', true)) {
            PaymentGateways::register(
                $this->config('gateway.id', PaypalPaymentGateway::DEFAULT_ID),
                PaypalPaymentGateway::class
            );
        }

        if ($this->config('bind', true)) {
            $this->app->bind(PaypalPaymentGateway::class, function ($app) {
                return new PaypalPaymentGateway(
                    $this->config('client_id'),
                    $this->config('secret'),
                    $this->config('return_url'),
                    $this->config('cancel_url'),
                    $this->config('sandbox')
                );
            });
        }

        $this->publishes([
            $this->getBasePath() . '/' . $this->concord->getConvention()->viewsFolder() =>
            resource_path('views/vendor/paypal'),
            'paypal'
        ]);
    }
}
