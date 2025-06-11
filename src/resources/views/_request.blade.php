@if($autoRedirect)
    <p style="margin: 1rem 0">{{ $infoMessage ?? __('Now you will be redirected to the secure PayPal payment page to complete the purchase') }}</p>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            window.location = '{{ $url }}';
        });
    </script>
@endif
<div style="margin: 1rem 0">
    <a href="{{ $url }}">{{ $btnText ?? __('Proceed to the Payment') }}</a>
</div>

