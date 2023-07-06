<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class PgMethod extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_pg_method';
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
        $updatedAtOriginal = $this->update_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    public function getPaymentMethod($filterData, $limit, $pageNo) {
        try {
            $paymentMethodData = $this->newQuery();
            $paymentMethodData->with("AvailablePaymentMethods");
            if(isset($filterData['status']) && !empty($filterData['status'])) {
                $paymentMethodData->where('is_active', ($filterData['status'] == "active") ? 1 : 0);
            }
            if(isset($filterData['pg_name']) && !empty($filterData['pg_name'])) {
                $paymentMethodData->where('pg_name', $filterData['pg_name']);
            }
            if(isset($filterData['meta_code']) && !empty($filterData['meta_code'])) {
                $paymentMethodData->where('meta_code', $filterData['meta_code']);
            }
            if(isset($filterData['method_name']) && !empty($filterData['method_name'])) {
                $paymentMethodData->where('method_name', $filterData['method_name']);
            }
            if(isset($filterData['method_code']) && !empty($filterData['method_code'])) {
                $paymentMethodData->where('method_code', $filterData['method_code']);
            }
            if(isset($filterData['currency']) && !empty($filterData['currency'])) {
                $paymentMethodData->where('currency', $filterData['currency']);
            }
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $paymentMethodData->select([
                'pg_method_id',
                'pg_name',
                'method_name',
                'method_code',
                'currency',
                'is_seamless',
                'has_sub_method',
                'meta_code',
                'is_active',
                'priority',
                'created_at',
                'update_at',
            ]);
            $paymentMethodData->orderBy('created_at', 'desc');
            if($paymentMethodData->count() > 0){
                return $paymentMethodData->paginate($limit);
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

    public function addPaymentMethod($pgMethodId, $pgName, $metaCode, $methodName, $methodCode, $isSeamless, $hasSubMethod)
    {
        try {
            $this->pg_method_id = $pgMethodId;
            $this->pg_name = $pgName;
            $this->method_name = $methodName;
            $this->method_code = $methodCode;
            $this->meta_code = $metaCode;
            $this->is_seamless = $isSeamless;
            $this->has_sub_method = $hasSubMethod;
            $this->currency = "INR";
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

    public function AvailablePaymentMethods() {
        return $this->belongsTo(AvailablePgMethod::class, "pg_method_id", "pg_method_id");
    }
}
