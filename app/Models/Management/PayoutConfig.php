<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PayoutConfig extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_bank_transfer_config';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        "is_auto_transfer_enable" => "boolean",
        "is_manual_level_active" => "boolean",
        "is_auto_level_active" => "boolean"
    ];

    public function loadConfig() {
        try {
            $data = $this->orderBy("id", "desc")->first();
            if(isset($data)) {
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

    public function updateConfig($id, $updateData)
    {
        try {
            return $this->where("id", $id)->update($updateData);
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return false;
        }
    }
}
