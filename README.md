# safepay-laravel
Safepay Laravel Payment Gateway Integration


``composer require matiullah31/safepay-laravel``

Add provider in app.php

`` Priceoye\Safepay\SafepayProvider ``

Add alias

`` 'SafePay' => Priceoye\Safepay\SafepayFacade::class ``
  


### Publish the configuration file
  ``php artisan vendor:publish``
A file (safepay.php) will be placed in config folder.

```

return [
    "environment"  => env("SAFEPAY_ENV",'sandbox'), //use 'production' for live payments
    "api_key" => env("SAFEPAY_API_KEY",''),
    'redirect_url' => env("SAFEPAY_SUCCESS_URL",''),
    'cancel_url' => env("SAFEPAY_CANCEL_URL",''),
    'currency' => "PKR",
    'webhook_secret_key' => env("SAFEPAY_SECRET",'')
    'webhook_shared_secret_key' => env("SAFEPAY_SHARED_SECRET",''),
];

```
To make payment, you need to pass order_id and total amount in process_payment() 

```
use Priceoye\Safepay\Safepay;


$safepay = new Safepay;
$link = $safepay->process_payment($order_id, $cart_total);
```

In response, it will return a redirect link. Simply redirect the user
```
if($link['result'] == 'success')
    return redirect($link['redirect']); 
```

When payment is done on safepay, you will be redirected to redirect_url that you passed in config file. 

Safepay will post this data in redirect url.
```
array (
  'order_id' => '1',
  'sig' => '215557faae130d4b65dbd30b1838b816bb03c08531d861fd1215a836aaab5188',
  'reference' => '532860',
  'tracker' => 'track_93281536-3687-44a6-ac2b-4d015f98ef46',
  'token' => 'trans_eeedfc06-21d6-4c67-b1bc-941a7ac73e10',
) 
```

### Now create a route in web.php file

```
Route::post('payment-success', 'SafepayController@paymentCompleted');
Route::get('order-completed/{sig}', 'OrderController@orderCompleted')->name('orderCompleted');
Route::get('payment-cancel', 'OrderController@orderCancelled');
```

In VerifyCsrfToken.php middleware, add the following code
```
protected \$except = [
  'payment-success'
];
```

On payment-success route and paymentCompleted function. 
```
public function paymentCompleted(Request $request)
{
    $data = $request->input();
    $safepay = new Safepay;

    if ($safepay->validate_signature($data['tracker'], $data['sig']) === false) {

        return redirect()->route('checkout.index')->with(['error' => 'Payment Failed']);
    }

    return redirect(url('order-completed/'.$data['sig']));
    
}

```

### Now to add Webhook and verify it add route in api.php file

```

Route::post('api/safepayNotification', 'SafepayApiController@safepayNotification');

```

Now to verify the web hook request

```

public function safepayNotification(Request $request)
{
    $data = $request->all();

    $safepay = new Safepay;

    $x_sfpy_signature = $request->header('x-sfpy-signature');

    if ($safepay->verifyWebhook($data, $x_sfpy_signature) === false) {

        //Web Hook verification failed
       
    }

   
    
}


```