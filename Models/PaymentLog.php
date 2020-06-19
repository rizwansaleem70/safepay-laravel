<?php
namespace Webribs\Safepay\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $table = "payment_logs";
    
    protected $fillable = [
        'tracker',
        'reference_code',
        'order_id',
        'signature'
    ];

    public function order()
    {
        return $this->belongsTo(config('safepay.order_class'), 'order_id');
    }
}
