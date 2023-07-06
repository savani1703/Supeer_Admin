<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class VpnDetails extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_vpn_details';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    public $timestamps = false;


}
