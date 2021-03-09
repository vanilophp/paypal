# Examples

The Example below shows parts of the code that you can put in your application.

### CheckoutController

The controller below processes a submitted checkout, prepares the payment and returns the thank you
page with the prepared payment request:

```php
use Vanilo\Framework\Models\Order;
use Vanilo\Payment\Factories\PaymentFactory;
use Vanilo\Payment\Models\PaymentMethod;
use Vanilo\Payment\PaymentGateways;

class CheckoutController
{
    public function store(Request $request)
    {
        $order = Order::createFrom($request);
        $paymentMethod = PaymentMethod::find($request->get('paymentMethod'));
        $payment = PaymentFactory::createFromPayable($order, $paymentMethod);
        $gateway = PaymentGateways::make('paypal');
        $paymentRequest = $gateway->createPaymentRequest($payment);
        
        return view('checkout.thank-you', [
            'order' => $order,
            'paymentRequest' => $paymentRequest
        ]);
    }
}
```

### checkout/thank-you.blade.php

This sample blade template contains a thank you page where you can render the payment initiation
form:

**Blade Template:**

```blade
@extends('layouts.app')
@section('content')
    <div class="container">
        <h1>Thank you</h1>

        <div class="alert alert-success">Your order has been registered with number
            <strong>{{ $order->getNumber() }}</strong>.
        </div>

        <h3>Payment</h3>

        {!! $paymentRequest->getHtmlSnippet(); !!}
    </div>
@endsection
```

### PaypalReturnController

```php
class PaypalReturnController extends Controller
{
    public function cancel(Request $request)
    {
        $payment = Payment::findByPaymentId($request->get('orderId'));

        return view('payment.cancel', [ // The view is from your application
            'payment' => $payment,
            'order'   => $payment->getPayable(),
        ]);
    }

    public function return(Request $request)
    {
        Log::debug('PayPal return', $request->toArray());

        $response = PaymentGateways::make('paypal')->processPaymentResponse($request);
        $payment  = Payment::findByPaymentId($response->getPaymentId());

        if (!$payment) {
            // This returns an HTTP response in the format that Paypal understands
            return new ErrorResponseToPaypal(404, 'Could not locate payment with id ' . $response->getPaymentId());
        }

        if ($response->wasSuccessful()) {
            $payment->amount_paid = $response->getAmountPaid();
            if ($response->getAmountPaid() < $payment->getAmount()) {
                $payment->status = PaymentStatus::PARTIALLY_PAID();
                $payment->save();
                event(new PaymentPartiallyReceived($payment, $response->getAmountPaid()));
            } else {
                $payment->status = PaymentStatus::PAID();
                $payment->save();
                event(new PaymentCompleted($payment));
            }
        } else {
            $payment->status = PaymentStatus::DECLINED();
            $payment->save();
            event(new PaymentDeclined($payment));
        }
    }
}
```

### Routes

The routes for PayPal should look like:

```php
//web.php
Route::group(['prefix' => 'payment/paypal', 'as' => 'payment.paypal.'], function() {
    Route::post('return', 'PaypalReturnController@return')->name('return');
    Route::get('cancel', 'PaypalReturnController@cancel')->name('cancel');
});
```

**IMPORTANT!**: Make sure to **disable CSRF verification** for these URLs, by adding them as
exceptions to `app/Http/Middleware/VerifyCsrfToken`:

```php
class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/payment/paypal/*'
    ];
}
```

Have fun!

---
Congrats, you've reached the end of this doc! 🎉
