<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class PayoutCustomerInfo extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_customer_info';
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    public $timestamps = false;


}
