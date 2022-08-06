<?php 
namespace Priceoye\Safepay;

use Illuminate\Support\Facades\Facade;

class SafepayFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'safepay_facade';
    }
}