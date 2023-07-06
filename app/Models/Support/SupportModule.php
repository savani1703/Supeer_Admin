<?php

namespace App\Models\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 */
class SupportModule extends \Illuminate\Database\Eloquent\Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_support_panel_module';
    protected $primaryKey = 'module_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_route_module' => 'boolean',
        'is_child' => 'boolean',
        'is_child_is_route_module' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }

    public function getUpdatedAtIstAttribute() {
        $updatedAtOriginal = $this->updated_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }
}
