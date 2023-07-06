<?php

namespace App\Models\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SupportRoles extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_roles';
    protected $primaryKey = 'role_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_delete_allowed' => 'boolean'
    ];

    protected $appends = [
        "created_at_ist",
    ];

    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }
}
