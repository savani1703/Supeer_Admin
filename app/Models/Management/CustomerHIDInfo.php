<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CustomerHIDInfo extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_customer_hid_details';
    protected $primaryKey = 'device_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_keep' => 'boolean',
        'is_get' => 'boolean'
    ];


}
