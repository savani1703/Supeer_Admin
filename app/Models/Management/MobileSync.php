<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
class MobileSync extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_mobile_sync';
    protected $primaryKey = 'hardware_id';
    public $incrementing = false;
    public $timestamps = false;

    public function getLastSuccessAtIstMindiffAttribute() {
        $updatedAtOriginal = $this->last_sync_date;
        if(isset($updatedAtOriginal)) {
            return Carbon::now()->diffInMinutes(Carbon::parse($updatedAtOriginal, "UTC"));
        }
        return 20;
    }

}
