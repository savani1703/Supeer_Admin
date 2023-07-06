<?php

namespace App\Models\Management;


use App\Constant\PaymentStatus;
use App\Constant\RefundStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class MerchantPaymentMeta extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_merchant_payment_meta';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

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

    public function getMerchantPaymentMeta($merchantId) {
        try {
            $paymentMeta = $this->newQuery();
            $paymentMeta->where("merchant_id", $merchantId);
            $paymentMeta->select([
                'merchant_id',
                'pg_id',
                'pg_name',
                'is_active',
                'is_delete',
                'is_visible',
                'is_seamless',
                'daily_limit',
                'min_amount',
                'max_amount',
                'current_turnover',
                'pg_type',
                'payment_method',
                'is_level1',
                'is_level2',
                'is_level3',
                'is_level4',
                'created_at',
                'updated_at'
            ]);
            $paymentMeta->orderBy('created_at', 'desc');
            $result = $paymentMeta->get();
            if($result->count() > 0){
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

    public function getPayInMeta($merchantId, $filterData)
    {
        try {
            $payInMeta = $this->newQuery();
            $payInMeta->where("merchant_id", $merchantId);
            $payInMeta->where("is_delete", "0");

            if(isset($filterData)) {
                if(isset($filterData['pg_id'])) {
                    $payInMeta->where("pg_id", $filterData['pg_id']);
                }
                if(isset($filterData['pg_name'])) {
                    $payInMeta->where("pg_name", strtoupper($filterData['pg_name']));
                }
                if(isset($filterData['pg_type'])) {
                    $payInMeta->where("pg_type", strtoupper($filterData['pg_type']));
                }
                if(isset($filterData['payment_method'])) {
                    $payInMeta->where("payment_method", strtoupper($filterData['payment_method']));
                }
            }

            $payInMeta->orderBy('is_active', 'desc');
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
public function getMerchantMetaInfoByID($meta_pay_id)
{
    try {
        $value = Cache::remember('meta_info'.$meta_pay_id, 60, function () use ($meta_pay_id) {
            return $this->where("id", $meta_pay_id)->first(['pg_id','pg_name']);
        });
        return $value;
    } catch (QueryException $ex) {
        Log::error('Error while executing SQL Query', [
            'class' => __CLASS__,
            'function' => __METHOD__,
            'file' => $ex->getFile(),
            'line_no' => $ex->getLine(),
            'error_message' => $ex->getMessage(),
        ]);

    }
    return null;
}
    public function getReadPayInMeta()
    {
        try {
            $payInMeta = $this->newQuery();
            $payInMeta->where("is_active", "1");
            $payInMeta->where("merchant_id", "MID_3UOP4XZR4OO17D")->orWhere('merchant_id',   "MID_XW9HUFIH2YMBLD")->orWhere('merchant_id',   "MID_KA4NK1DHME46ZE")->orWhere('merchant_id',   "MID_NYDK1MJAMV54T6");
            $payInMeta->orderBy('merchant_id');
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

    public function updatePayInMetaStatus($merchantId, $pgName, $id, $pgId, $status)
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

    public function checkActiveMetaIsExists($merchantId)
    {
        try {
            return $this->where("merchant_id", $merchantId)->where("is_active", "1")->exists();
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
    public function checkMetaIsExists($merchantId, $pgName, $pgId, $paymentMethod)
    {
        try {
            return $this->where("merchant_id", $merchantId)->where("pg_id", $pgId)->where("pg_name", $pgName)->where("payment_method", $paymentMethod)->exists();
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

    public function updateMerchantCollectionMeta($merchantId, $pgName, $pgId, $paymentMethod)
    {
        try {
            return $this->where("merchant_id", $merchantId)->where("pg_id", $pgId)->where("pg_name", $pgName)->where("payment_method", $paymentMethod)->update([
                "is_active" => '1',
                "is_delete" => '0',
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

    public function updatePayInMetaMinLimit($merchantId, $pgName, $id, $pgId, $minLimit)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update(["min_amount" => $minLimit])) {
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

    public function updatePayInMetaMaxLimit($merchantId, $pgName, $id, $pgId, $maxLimit)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update(["max_amount" => $maxLimit])) {
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

    public function updatePayInMetaDailyLimit($merchantId, $pgName, $id, $pgId, $dailyLimit)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update(["daily_limit" => $dailyLimit])) {
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

    public function updatePayInMetaLevel($merchantId, $pgName, $id, $pgId, $levelKey, $status)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update([$levelKey => $status])) {
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

    public function deletePayInMeta($merchantId, $pgName, $id, $pgId)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update(["is_active" => "0", "is_delete" => "1"])) {
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

    public function getAllPayInMeta($merchantId)
    {
        try {
            $result = $this->where('merchant_id', $merchantId)->get();
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

    public function addMerchantCollectionMeta($merchantId,
                                              $pgName,
                                              $pgId,
                                              $paymentMethod,
                                              $isSeamless,
                                              $pgType,
                                              $level1,
                                              $level2,
                                              $level3,
                                              $level4
    )
    {
        try {
            $this->merchant_id = $merchantId;
            $this->pg_name = $pgName;
            $this->pg_id = $pgId;
            $this->payment_method = $paymentMethod;
            $this->pg_type = $pgType;
            $this->is_seamless = $isSeamless;
            $this->is_active = true;
            $this->min_amount = 100;
            $this->max_amount = 100000;
            $this->daily_limit = 100000;
            $this->is_level1 = $level1;
            $this->is_level2 = $level2;
            $this->is_level3 = $level3;
            $this->is_level4 = $level4;
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



    public function is_bank_Active($av_bank_id)
    {
        try {
            return $this ->where(function ($query) {
                $query->where('merchant_id', 'MID_KM7WHHR9EXE1SN');
            })->where("pg_id", $av_bank_id)->where("is_active", true)->orderBy('created_at','desc')->first();
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
        return null;
    }

    public function currentTurnOver($av_bank_id)
    {
        try {
            $result = $this->where('pg_id', $av_bank_id)->sum('current_turnover');
            if($result) {
                return $result;
            }
            return null;
        } catch (QueryException $ex) {
            report($ex);
            return null;
        }
    }

    public function getLastMetaDetails($av_bank_id)
    {
        try {
            return $this ->where(function ($query) {
                $query->where('merchant_id', 'MID_3UOP4XZR4OO17D')
                    ->orWhere('merchant_id','MID_XW9HUFIH2YMBLD')
                    ->orWhere('merchant_id','MID_NYDK1MJAMV54T6')
                    ->orWhere('merchant_id','MID_KA4NK1DHME46ZE')
                    ->orWhere('merchant_id','MID_DRCL3TTEEFFTDT')
                    ->orWhere('merchant_id','MID_2TYKNS2KMZ25RZ');
            })->where("pg_id", $av_bank_id)->orderBy('created_at','desc')->first();
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
        return null;
    }

    public function getMetaMerchantDetails($merchantId, $value)
    {
        try {
            $merchantDetails = $this->where('merchant_id', $merchantId)
                ->where('is_active', $value)
                ->where('is_delete', false)
                ->where('is_visible', true)
                ->get();
            if($merchantDetails->count()){
                return $merchantDetails;
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

    public function updateMerchantAllMeta($merchantId, $updateVal)
    {
        try {
            $this->where('merchant_id', $merchantId)
                ->where('is_active','!=',$updateVal)
                ->where('is_delete', false)
                ->where('is_visible', true)
                ->update(['is_active' => $updateVal]);

            return true;
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function updatePayInMetaPerLimit($merchantId, $pgName, $id, $pgId, $perLimit)
    {
        try {
            if($this->where("merchant_id", $merchantId)->where("id", $id)->where("pg_name", $pgName)->where("pg_id", $pgId)->update(["per_limit" => $perLimit])) {
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

    public function getSpecificActiveMerchantList()
    {
        try {
            $result = $this ->where(function ($query) {
                $query->where('merchant_id', 'MID_XW9HUFIH2YMBLD')
                    ->orWhere('merchant_id','MID_3UOP4XZR4OO17D')
                    ->orWhere('merchant_id','MID_KA4NK1DHME46ZE')
                    ->orWhere('merchant_id','MID_NYDK1MJAMV54T6')
                    ->orWhere('merchant_id','MID_2TYKNS2KMZ25RZ');
            })->where("pg_type", 'MANUAL')
                ->where("is_active", true)
                ->orderBy('created_at','desc')
                ->get();
            if($result->count()){
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
}
