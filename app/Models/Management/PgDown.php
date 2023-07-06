<?php

namespace App\Models\Management;

use App\Classes\Util\TimeCode;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PgDown extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_pg_down_v1';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function getPendingNotice()
    {
        try {
            $startDate = Carbon::now()->subMinutes(30)->toDateTimeString();
            $data = $this->where("isget", 0)->whereNull('data_type')
                ->where("created_at", '>',$startDate)
                ->orderBy("created_at", "ASC")->first();
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

    public function getPendingBlockNotice()
    {
        try {
            $startDate = Carbon::now()->subMinutes(5)->toDateTimeString();
            $data = $this->where("isget", 0)->where(function ($query) {
                $query->where("data_type", "UPIPAYSRISK");
                $query->orWhere("data_type", "BLOCKED");
            })->where("created_at", '>',$startDate)->orderBy("created_at", "ASC")->first();
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

    public function getPendingPayoutNotice()
    {
        try {
            $data = $this->where("isget", 0)->where("data_type", "PAYOUT")->orderBy("created_at", "ASC")->first();
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

    public function getPendingPayoutNoticeForOtp()
    {
        try {
            $data = $this->where("isget", 0)->where("data_type", "PAYOUT_OTP")->orderBy("created_at", "ASC")->first();
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

    public function getPendingNoticeForVpn()
    {
        try {
            $data = $this->where("isget", 0)->where("data_type", "BLOCKED_BY_VPN")->orderBy("created_at", "ASC")->first();
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

    public function getPendingNoticeForLateSync()
    {
        try {
            $data = $this->where("isget", 0)->where("data_type", "LATE_BANK_SYNC")->orderBy("created_at", "ASC")->first();
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

    public function setError($transactionId, $pgMid, $pgName, $reason, $txtException, $dataType =null, $timeCode = null){
        try {
            $timeStamp = Carbon::now()->subMinute(60);
            if(isset($timeCode) && !empty($timeCode)){
                if(strcmp($timeCode,TimeCode::LATE_BANK_SYNC) === 0){
                    $timeStamp = Carbon::now()->subMinute(2);
                }
            }
            if($this->where('transaction_id',$transactionId)->where('pg_mid',$pgMid)->where('pg_name',$pgName)->where('reason',$reason)->where('txt_exception',$txtException)->where('data_type',$dataType)->where('created_at','>', $timeStamp)->exists() === false) {
                $this->transaction_id = $transactionId;
                $this->pg_mid = $pgMid;
                $this->pg_name = isset($pgName) ? $pgName : "N/A";
                $this->reason = $reason;
                $this->txt_exception = is_array($txtException) ? json_encode($txtException) : $txtException;
                $this->data_type = $dataType;
                if ($this->save()) {
                    return true;
                }
            }
            return false;
        }catch (QueryException $ex){
            Log::info('PgDownModel Errors ',['setError' => $ex->getMessage()]);
            return false;
        }
    }

}
