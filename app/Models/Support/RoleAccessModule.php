<?php

namespace App\Models\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class RoleAccessModule extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_roles_access_module';
    protected $primaryKey = 'access_id';
    public $incrementing = false;
    public $timestamps = false;

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

    public function getByRoleId($roleId)
    {
        try {
            $data = $this->where("role_id", $roleId)->with("supportModule")->get();
            if($data->count() > 0) {
                return $data;
            }
            return null;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return null;
        }
    }

    public function supportModule() {
        return $this->belongsTo(SupportModule::class, "module_id", "module_id");
    }

}
