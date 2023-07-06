<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class PayoutCustomer extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_customer';
    protected $primaryKey = 'payout_id';
    public $incrementing = false;
    public $timestamps = false;

    
}
