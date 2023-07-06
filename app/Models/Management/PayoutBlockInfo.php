<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class PayoutBlockInfo extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_block_info';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        "is_block" => "boolean"
    ];

}
