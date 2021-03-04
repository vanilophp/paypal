<form method="post" action="{{ $url }}" name="paypal" target="_self">
    {{-- fields here --}}

    @if($autoRedirect)
        <p>{{ __('You will be redirected to the secure payment page') }}</p>
        <p>
            <img src="{{ 'paypal image **absolute** url here' }}" alt="" title=""
                 onload="javascript:document.paypal.submit()">
        </p>
    @endif
        <button type="submit">
            {{ __('Proceed to Payment') }}
        </button>
</form>
