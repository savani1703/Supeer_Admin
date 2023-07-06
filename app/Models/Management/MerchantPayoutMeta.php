<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class MerchantPayoutMeta extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_merchant_payout_meta';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        "is_active" => "boolean"
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

    public function getPayoutMeta($merchantId, $filterData)
    {
        try {
            $payInMeta = $this->newQuery();
            $payInMeta->where("merchant_id", $merchantId);
            $payInMeta->where("is_deleted", 0);

            if(isset($filterData)) {
                if(isset($filterData['pg_id'])) {
                    $payInMeta->where("pg_id", $filterData['pg_id']);
                }
                if(isset($filterData['pg_name'])) {
                    $payInMeta->where("pg_name", strtoupper($filterData['pg_name']));
                }
            }

            $payInMeta->orderBy('created_at', 'desc');

            $result = $payInMeta->get();

            if($result->count() > 0) {
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
            return false;
        }
    }

    public function updatePayoutMetaStatus($merchantId, $pgName, $id, $pgId, $status)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update(["is_active" => $status])) {
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


    public function getAllPayoutMeta($merchantId)
    {
        try {
            return $this->where('merchant_id', $merchantId)->get();
        }catch (QueryException $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            throw $ex;
        }
    }

    public function checkMetaIsExists($merchantId, $pgName, $pgId)
    {
        try {
            return $this->where("merchant_id", $merchantId)->where("pg_id", $pgId)->where("pg_name", $pgName)->exists();
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

    public function updateMerchantWithdrawalMeta($merchantId, $pgName, $pgId, $pgLabel)
    {
        try {
            return $this->where("merchant_id", $merchantId)->where("pg_id", $pgId)->where("pg_name", $pgName)->update([
                "is_active" => '1',
                "pg_label" => $pgLabel,
            ]);
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

    public function disableMerchantMetaMeta($metaId, $pgName)
    {
        try {
            return $this->where("merchant_id", "!=", "MID_8WUBWQL6I4KJZT")->where("pg_id", $metaId)->where("pg_name", $pgName)->update([
                "is_active" => '0'
            ]);
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

    public function addMerchantCollectionMeta($merchantId, $pgName, $pgId, $pgLabel)
    {
        try {
            $this->merchant_id = $merchantId;
            $this->pg_name = $pgName;
            $this->pg_id = $pgId;
            $this->pg_label = $pgLabel;
            if($this->save()){
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


}
