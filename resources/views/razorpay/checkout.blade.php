<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Razorpay Checkout</title>
  </head>
  <body>
    <div style="max-width:720px;margin:2rem auto;font-family:sans-serif;">
      <h1>Complete payment</h1>
      <p>You're being redirected to Razorpay to complete the payment.</p>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        (function(){
            const options = {
                "key": "{{ env('RAZORPAY_KEY_ID') }}",
                "amount": {{ $amount }}, // in paise
                "currency": "{{ $currency ?? 'INR' }}",
                "name": "{{ config('app.name') }}",
                "order_id": "{{ $order_id }}",
                "handler": function (response){
                    // On success, redirect to signup success with params
                    window.location = "{{ route('signup.success') }}?payment_id=" + response.razorpay_payment_id + "&order_id=" + response.razorpay_order_id;
                },
                "modal": { "ondismiss": function(){ window.location = "{{ route('signup.show') }}"; } }
            };

            const rzp = new Razorpay(options);
            rzp.open();
        })();
    </script>
  </body>
</html>
