<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class TransactionNotice extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_notice_v1';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function getPendingNotice()
    {
        try {
            $data = $this->where("isget", 0)->orderBy("created", "ASC")->first();
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

    public function markAsGet($id) {
        try {
            $this->where("id", $id)->update(["isget" => 1]);
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }

}
