<?php

namespace App\Models\PaymentManual;

use App\Classes\Util\PgName;
use App\Constant\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class BankConfig extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_bank_config';
    protected $primaryKey = 'bank_name';
    public $incrementing = false;
    public $timestamps = false;

    public function fetchStatus()
    {
        try {
            $data=$this->select([
                'bank_name',
            ])->where('is_down',1)->get();
            if ($data->count() > 0) {
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

    public function updateBankStatus($bank_name,$value)
    {
        try {
            return $this->where("bank_name",$bank_name)
                ->update(["is_down"=>$value]);
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

    public function getBankConfig()
    {
        try {
            $result = $this->orderBy('created_at','desc')->get();
            if ($result->count()) {
                return $result;
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

    public function checkBankIsDown($bankName){
        try {
            $result = $this->where('bank_name',$bankName)->where('is_down',1)->exists();
            if($result) {
                return true;
            }
            return false;
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
