<?php
namespace Rizwan\Safepay\Models;

use Illuminate\Database\Eloquent\Model;

class Safepay extends Model
{
    protected $fillable = [
        'tracker',
        'reference_code',
        'order_id',
        'signature'
    ];
}
