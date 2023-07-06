<?php

namespace App\Models\Management;


use App\Classes\Util\PgName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class PayoutCustomerLevel extends Model
{

    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_customer_level';
    protected $primaryKey = 'customer_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_get' => 'boolean'
    ];

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
         "last_success_at_ist",
    ];

    public function getCreatedAtIstAttribute()
    {
        $createdAtOriginal = $this->created_at;
        if (isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }

    public function getUpdatedAtIstAttribute()
    {
        $updatedAtOriginal = $this->updated_at;
        if (isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }
    public function getLastSuccessAtIstAttribute()
    {
        $successAtOriginal = $this->last_success_at;
        if (isset($successAtOriginal)) {
            return Carbon::parse($successAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $successAtOriginal;
    }



    public function getCustLevelData($filterData, $limit, $page_no)
    {
        try {
            $custData = $this->newQuery();
            if (isset($filterData) && sizeof($filterData) > 0) {

                if (isset($filterData['customer_id']) && !empty($filterData['customer_id'])) {
                    $custData->where('customer_id', $filterData['customer_id']);
                }
                if(isset($filterData['pg_type']) && !empty($filterData['pg_type']) && strcmp(strtolower($filterData['pg_type']), "all") !== 0) {
                    $custData->where('pg_name', $filterData['pg_type']);
                }
                if (isset($filterData['account_number']) && !empty($filterData['account_number'])) {
                    $custData->where('account_number', $filterData['account_number']);
                }
                if(isset($filterData['meta_id']) && !empty($filterData['meta_id'])) {
                    if(strcmp($filterData['meta_id'], "All") !== 0) {
                        $custData->where('meta_id', $filterData['meta_id']);
                    }
                }
                if (isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $custData->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($page_no) {
                return $page_no;
            });

            $custData->orderBy('created_at', 'desc');
            if ($custData->count() > 0) {
                return $custData->paginate($limit);
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

    public function addCustomerPayoutLevelDetails($customerId, $accountNumber, $accountHolderName, $bankName, $ifscCode, $pgName, $metaId, $lastMetaMerchantId, $lastSuccessAt, $updatedBy = null)
    {
        try {
            if (!$this->where('customer_id', $customerId)->where('account_number', $accountNumber)->exists()) {
                $this->customer_id = $customerId;
                $this->account_number = $accountNumber;
                $this->account_holder_name = $accountHolderName;
                $this->bank_name = $bankName;
                $this->ifsc_code = $ifscCode;
                $this->pg_name = $pgName;
                $this->meta_id = $metaId;
                $this->last_meta_merchant_id = $lastMetaMerchantId;
                $this->last_success_at = $lastSuccessAt;
                $this->updated_by = $updatedBy;
                if ($this->save()) {
                    return true;
                }
            }else{
                $this->where('customer_id', $customerId)->update(['pg_name' => $pgName, 'meta_id' => $metaId, 'last_meta_merchant_id' => $lastMetaMerchantId, 'last_success_at' => $lastSuccessAt,'updated_by' => $updatedBy]);
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


    public function updateCustomerPayoutLevelDetails($customerId, $pgName, $metaId, $lastMetaMerchantId, $lastSuccessAt, $updatedBy = null)
    {
        try {
            $result = $this->where('customer_id', $customerId)->update(['pg_name' => $pgName, 'meta_id' => $metaId, 'last_meta_merchant_id' => $lastMetaMerchantId, 'last_success_at' => $lastSuccessAt,'updated_by' => $updatedBy]);
            if($result){
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

    public function getCustLevelDataById($customerId)
    {
        try {
            $result = $this->where('customer_id',$customerId)->first();
            if($result){
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

    public function getEligibleCustomerIDByMeta($customerIds, $metaId)
    {
        try {
            $result = $this->whereIn('customer_id', $customerIds)->where('meta_id', $metaId)->pluck('customer_id')->toArray();
            if(count($result) > 0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getDetails()
    {
        try {
            $result = $this->where('is_get',false)->limit(100)->get();
            if($result){
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

    public function markAsGet($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)->update(['is_get' => true]);
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

    public function getDetailsForShift()
    {
        try {
            $result = $this->where('pg_name',PgName::BULKPE)->where('is_shift',false)->limit(1200)->get();
            if($result){
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

    public function customerShift($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)->where('pg_name', PgName::BULKPE)->where('is_shift',false)->update(['is_shift' => true,'pg_name' => 'IDFC', 'meta_id' => 'ID017','last_meta_merchant_id' => '10134228963']);
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

}
