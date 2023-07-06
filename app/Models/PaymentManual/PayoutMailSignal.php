<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;

class PayoutMailSignal extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_payout_mail_signal';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    
}
