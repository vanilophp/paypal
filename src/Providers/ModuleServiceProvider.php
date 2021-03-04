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
use Vanilo\Paypal\PaypalPaymentGateway;
use Vanilo\Payment\PaymentGateways;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
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
                    // @todo add config values here
                    // $this->config('asd')
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
