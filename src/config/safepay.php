<?php 


return [
    "environment"  => env("SAFEPAY_ENV",'sandbox'), //use 'production' for live payments
    "api_key" => env("SAFEPAY_API_KEY",''),
    'redirect_url' => env("SAFEPAY_SUCCESS_URL",''),
    'cancel_url' => env("SAFEPAY_CANCEL_URL",''),
    'currency' => "PKR",
    'webhook_secret_key' => env("SAFEPAY_SECRET",'')
    'webhook_shared_secret_key' => env("SAFEPAY_SHARED_SECRET",''),
];