# PayPal Payment Workflow

The typical Vanilo Payments workflow with PayPal consists of the following steps:

1. Create an **Order** (or any ["Payable"](https://vanilo.io/docs/2.x/payments#payables))
2. Obtain the **payment method** from the checkout<sup>*</sup>
3. Get the appropriate **gateway instance** associated with the payment method
4. Generate a **payment request** using the gateway
5. Inject the **HTML snippet** on the checkout/thankyou page
6. The HTML snippet **redirects** the Consumer to the **processor's payment page**
7. @todo add PayPal specific parts here

> *: If your site's one and only payment method is PayPal (ie. no cash on delivery, etc), then step 2 might not be necessary.

## Obtain Gateway Instance

Once you have an order (or any other payable), then the starting point of payment operations is
obtaining a gateway instance:

```php
$gateway = \Vanilo\Payment\PaymentGateways::make('paypal');
// Vanilo\Paypal\PaypalPaymentGateway
```

The gateway provides you two essential methods:

- `createPaymentRequest` - Assembles the payment initiation request from an order (payable) that can be injected on your checkout page.
- `processPaymentResponse` - Processes the HTTP response returning from Paypal after a payment attempt.

## Starting Online Payments

**Controller:**

```php
use Vanilo\Framework\Models\Order;
use Vanilo\Payment\Factories\PaymentFactory;
use Vanilo\Payment\Models\PaymentMethod;
use Vanilo\Payment\PaymentGateways;

class OrderController
{
    public function submit(Request $request)
    {
        $order = Order::createFrom($request);
        $paymentMethod = PaymentMethod::find($request->get('paymentMethod'));
        $payment = PaymentFactory::createFromPayable($order, $paymentMethod);
        $gateway = PaymentGateways::make('paypal');
        $paymentRequest = $gateway->createPaymentRequest($payment);
        
        return view('order.confirmation', [
            'order' => $order,
            'paymentRequest' => $paymentRequest
        ]);
    }
}
```

**Blade Template:**

```blade
{!! $paymentRequest->getHtmlSnippet(); !!}
```

The generated HTML snippet will contain a prepared, encrypted HTML Form with all the necessary
details that can be submitted to PayPal right from the consumer's browser to start the payment
process.

You can pass an array to the `getHtmlSnippet()` method that recognizes the following keys:

- `autoRedirect`: bool, which if true, the rendered payment request from will automatically submit
  itself on load towards PayPal.

```blade
{!! $paymentRequest->getHtmlSnippet(['autoRedirect' => true]); !!}
```

### Payment Request Options

The gateway's `createPaymentRequest` method accepts additional parameters that can be used to
customize the generated request.

The signature of the method is the following:

```php
public function createPaymentRequest(
    Payment $payment,
    Address $shippingAddress = null,
    array $options = []
    ): PaymentRequest
```

1. The first parameter is the `$payment`. Every attempt to settle a payable is a new `Payment` record.
2. The second one is the `$shippingAddress` in case it differs from billing address. It can be left NULL.
3. The third parameters is an array with possible `$options`.

You can pass the following values in the `$options` array:

| Array Key     | Example                                | Description                                                                                                                                      |
|:--------------|:---------------------------------------|:-------------------------------------------------------------------------------------------------------------------------------------------------|
| `key_here`    | @todo add here any paypal values       |                                                                                                                                                  |
| `description` | Order with number XYZ                  | By default it's "Order no. XXX", but you can pass any value here that will be visible on PayPal as description.                                  |
| `view`        | `payment.paypal._form`                 | By default it's `paypal::_request` You can use a custom blade view to render the HTML snippet instead of the default one this library provides.  |

**Example**:

```php
$options = [
    'description' => 'Order with number ' . $order->number,
    'view'=> 'payment.paypal._form',
];
$gateway->createPaymentRequest($payment, null, $options);
```

#### Customizing The Generated HTML

Apart from passing the `view` option to the `createPaymentRequest` (see above), there's an even more
simple way: Laravel lets you
[override the views from vendor packages](https://laravel.com/docs/8.x/packages#overriding-package-views)
like this.

Simply put, if you create the `resources/views/vendor/paypal/_request.blade.php` file in your
application, then this blade view will be used instead of the one supplied by the package.

To get the default view from the package and start customizing it, use this command:

```bash
php artisan vendor:publish --tag=vanilo-paypal
```

This will copy the default blade view used to render the HTML form into the
`resources/views/vendor/paypal/` folder of your application. After that, the `getHtmlSnippet()`
method will use the copied blade template to render the HTML snippet for PayPal payment requests.

## Confirm And Return URLs

Document PayPal specific handling of these URLs here

---

**Next**: [Examples &raquo;](examples.md)
