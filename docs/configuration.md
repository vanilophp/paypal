# Configuration

## Dotenv Values

The following `.env` parameters can be set in order to work with this package.

```dotenv
PAYPAL_CLIENT_ID=test-client-id
PAYPAL_SECRET=test-secret
PAYPAL_SANDBOX=true
PAYPAL_RETURN_URL=http://app.com/return/{paymentId}
PAYPAL_CANCEL_URL=http://app.com/cancel?pid={paymentId}
```

### Return and Cancel URLs

The return and cancel URLs are not defined by this library, but by your
application. Routes, controllers, etc need to be set up in your app and
be passed to this library by setting them in the `PAYPAL_RETURN_URL` and
`PAYPAL_CANCEL_URL` env vars.

In order to identify the payment from a Paypal callback, your route
needs to contain the payment id. It's up to you, whether you want to get
it as a query or as a route param. The only requirement is to set have
it somewhere, so that you can identify it.

The `{paymentId}` parameter in the value will be replaced with the
actual payment id.

**Return URL Examples**:

- `PAYPAL_RETURN_URL=https://yourapp.com/payment/paypal/{paymentId}/return`
- `PAYPAL_RETURN_URL=https://yourapp.com/ppret?payment={paymentId}`
- `PAYPAL_RETURN_URL=https://yourapp.com/paypal?id={paymentId}&op=return`
- `PAYPAL_RETURN_URL=/checkout/return/{paymentId}`

**Cancel URL Examples**:

- `PAYPAL_CANCEL_URL=https://yourapp.com/payment/paypal/{paymentId}/cancel`
- `PAYPAL_CANCEL_URL=https://yourapp.com/ppcancel?payment={paymentId}`
- `PAYPAL_CANCEL_URL=https://yourapp.com/paypal?id={paymentId}&op=cancel`
- `PAYPAL_CANCEL_URL=/checkout/cancel/{paymentId}`

If you pass a relative path, the library will use Laravel's `URL::to()`
method to convert it to an absolute url of your application. This can
be convenient using the same settings for dev/staging/prod environments.

## Registration with Payments Module

The module will automatically register the payment gateway with the Vanilo Payments registry by
default. Having that, you can get a gateway instance directly from the Payment registry:

```php
$paypalGateway = \Vanilo\Payment\PaymentGateways::make('paypal');
```

### Registering With Another Name

If you'd like to use another name in the payment registry, it can be done in the module config:

```php
//config/concord.php
return [
    'modules' => [
        //...
        Vanilo\Paypal\Providers\ModuleServiceProvider::class => [
            'gateway' => [
                'id' => 'maffia'
            ]
        ]
        //...
    ]
];
```

After this you can obtain a gateway instance with the configured name:

```php
\Vanilo\Payment\PaymentGateways::make('maffia');
```

### Prevent from Auto-registration

If you don't want it to be registered automatically, you can prevent it by changing the module
configuration:

```php
//config/concord.php
return [
    'modules' => [
        //...
        Vanilo\Paypal\Providers\ModuleServiceProvider::class => [
            'gateway' => [
                'register' => false
            ]
        ]
        //...
    ]
];
```

### Manual Registration

If you disable registration and want to register the gateway manually you can do it by using the
Vanilo Payment module's payment gateway registry:

```php
use Vanilo\Paypal\PaypalPaymentGateway;
use Vanilo\Payment\PaymentGateways;

PaymentGateways::register('paypal-or-whatever-name-you-want', PaypalPaymentGateway::class);
```

## Binding With The Laravel Container

By default `PaypalPaymentGateway::class` gets bound to the Laravel DI container, so that you can
obtain a properly autoconfigured instance. Typically, you don't get the instance directly from the
Laravel container (ie. `app()->make(PaypalPaymentGateway::class)`) but from the Vanilo Payment
Gateway registry:

```php
$instance = \Vanilo\Payment\PaymentGateways::make('paypal');
```

The default DI binding happens so that all the configuration parameters are read from the config values
mentioned above. This will work out of the box and will be sufficient for most of the applications.

### Manual Binding

It is possible to prevent the automatic binding and thus configure the Gateway in a custom way in
the module config:

```php
//config/concord.php
return [
    'modules' => [
        Vanilo\Paypal\Providers\ModuleServiceProvider::class => [
            'bind' => false,
```

This can be useful if the Gateway configuration can't be set in the env file, for example when:

- The credentials can be **configured in an Admin interface** instead of `.env`
- Your app has **multiple payment methods** that use PayPal with **different credentials**
- There is a **multi-tenant application**, where each tenant has their own credentials

Setting `vanilo.paypal.bind` to `false` will cause that the class doesn't get bound with the
Laravel DI container automatically. Therefore, you need to do this yourself in your application,
typically in the `AppServiceProvider::boot()` method:

```php
$this->app->bind(PaypalPaymentGateway::class, function ($app) {
    return new PaypalPaymentGateway(
        config('vanilo.paypal.client_id'),  // You can use any source
        config('vanilo.paypal.secret'),     // other than config()
        config('vanilo.paypal.return_url'), // for passing args
        config('vanilo.paypal.cancel_url'),
        config('vanilo.paypal.sandbox')
    );
});
```

---

**Next**: [Workflow &raquo;](workflow.md)
