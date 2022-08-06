<?php

namespace Priceoye\Safepay;

use Priceoye\Safepay\Safepay;
use Illuminate\Support\ServiceProvider;

class SafepayProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->publishes([
            __DIR__."/config/safepay.php" => config_path("safepay.php")
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind("safepay_facade", function(){
            return new Safepay;
        });
        // $this->app->make(SafepayHandler::class);
    }
}
