<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PayoutWhiteListClient extends  Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_whitelist_client';
    protected $primaryKey = 'merchant_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        "is_active" => "boolean",
        "is_manual_payout" => "boolean"
    ];

    public function getAllClientList() {
        try {
            $data = $this->with("merchantDetails")->orderBy("merchant_id", "desc")->get();
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

    public function addClient($clientName) {
        try {
            $this->merchant_id = $clientName;
            $this->is_active = true;
            if($this->save()) {
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

    public function updateClientStatus($clientName, $status) {
        try {
            if($this->where("merchant_id", $clientName)->update(["is_active" => $status])) {
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

    public function updateClientStatusManual($clientName, $isManualPayout) {
        try {
            if($this->where("merchant_id", $clientName)->update(["is_manual_payout" => $isManualPayout])) {
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

    public function getActiveManualMerchantPayoutList()
    {
        try {
            $result = $this->with("merchantDetails")->where('is_manual_payout', true)->get();
            if($result->count()){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
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


    public function checkMerchantIsActive($merchantId)
    {
        try {
            $result = $this->where("merchant_id", $merchantId)->where('is_manual_payout', true)->exists();
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
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

    public function merchantDetails()
    {
        return $this->belongsTo(MerchantDetails::class, "merchant_id", "merchant_id");
    }

    public function editwhitelistlimit($mid,$value,$columnName) {
        try {
            if($this->where("merchant_id", $mid)->update([$columnName => $value])) {
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
