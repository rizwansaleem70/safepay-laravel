# safepay-laravel
Safepay Laravel Payment Gateway Integration


``composer require rizwansaleem70/safepay-laravel``

Add provider in app.php

`` rizwansaleem70\Safepay\SafepayProvider ``

Add alias

`` 'SafePay' => rizwansaleem70\Safepay\SafepayFacade::class ``
  

### Publish the migration file.
  ``php artisan migrate``

It will create a table named "payment_logs". 


### Publish the configuration file
  ``php artisan vendor:publish``
A file (safepay.php) will be placed in config folder.

```
return [
    "environment"  => "sandbox", //use 'production' for live payments
    "api_key" => "",
    'redirect_url' => "http://localhost:8000/success",
    'cancel_url' => "http://localhost:8000/payment-cancel",
    'currency' => "PKR",
    'webhook_secret_key' => "",
    'order_class' => Order::class
];
```
To make payment, you need to pass order_id and total amount in process_payment() 

```
use Rizwansaleem70\Safepay\Safepay;


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
Route::post('success', 'Front\HomeController@storePaymentLog');
Route::get('payment-success/{sig}', 'Front\HomeController@viewPaymentSuccessPage')->name('payment_success');
Route::get('payment-cancel', 'Front\HomeController@viewCancelPaymentPage');
```

In VerifyCsrfToken.php middleware, add the following code
```
protected \$except = [
  'success'
];
```

In storePaymentLog method you need to validate the signature. If signature is validated then store the payment log and update the order status. 
```
public function storePaymentLog(Request $request)
{
    $data = $request->input();
    $safepay = new Safepay;

    if ($safepay->validate_signature($data['tracker'], $data['sig']) === false) {
        return redirect()->route('checkout.index')->with(['error' => 'Payment Failed']);
    }

    PaymentLog::create([
        'order_id' => $data['order_id'],
        'reference_code' => $data['reference'],
        'tracker' => $data['tracker'],
        'signature' => $data['sig'],
    ]);

    //update order status
    $order = Order::find($data['order_id']);
    $order->order_status_id = 1; //Paid
    $order->save();

    event(new OrderCreateEvent($order));

    Cart::destroy();

    return redirect()->route('payment_success', $data['sig']);
}

