<?php

namespace App\Models\Management;

use App\Classes\Util\RefundInternalStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class Refund extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_refund';
    protected $primaryKey = 'refund_id';
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

    public function getRefund($filterData, $limit, $page_no)
    {
        try {

            $refundData = $this->newQuery();
            $refundData->with("merchantDetails");
            if (isset($filterData) && sizeof($filterData) > 0) {

                if (isset($filterData['refund_id']) && !empty($filterData['refund_id'])) {
                    $refundData->where('refund_id', $filterData['refund_id']);
                }
                if (isset($filterData['transaction_id']) && !empty($filterData['transaction_id'])) {
                    $refundData->where('transaction_id', $filterData['transaction_id']);
                }
                if (isset($filterData['merchant_id']) && !empty($filterData['merchant_id'])) {
                    $refundData->where('merchant_id', $filterData['merchant_id']);
                }
                if (isset($filterData['refund_amount']) && !empty($filterData['refund_amount'])) {
                    $refundData->where('refund_amount', $filterData['refund_amount']);
                }
                if (isset($filterData['pg_name']) && !empty($filterData['pg_name'])) {
                    $refundData->where('pg_name', $filterData['pg_name']);
                }
                if (isset($filterData['bank_rrn']) && !empty($filterData['bank_rrn'])) {
                    $refundData->where('bank_rrn', $filterData['bank_rrn']);
                }
                if (isset($filterData['pg_ref_id']) && !empty($filterData['pg_ref_id'])) {
                    $refundData->where('pg_ref_id', $filterData['pg_ref_id']);
                }
                if (isset($filterData['status']) && !empty($filterData['status']) && strcmp($filterData['status'], "All") !== 0) {
                    $refundData->where('refund_status', $filterData['status']);
                }
                if (isset($filterData['internal_status']) && !empty($filterData['internal_status']) && strcmp($filterData['internal_status'], "All") !== 0) {
                    $refundData->where('internal_status', $filterData['internal_status']);
                }
                if (isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $refundData->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($page_no) {
                return $page_no;
            });

            $refundData->select([
                'refund_id',
                'merchant_id',
                'transaction_id',
                'refund_amount',
                'refund_status',
                'refund_type',
                'is_webhook_call',
                'internal_status',
                'refund_reason',
                'updated_at',
                'created_at',
            ]);

            $refundData->orderBy('created_at', 'desc');
            if ($refundData->count() > 0) {
                return $refundData->paginate($limit);
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

    public function getRefundAmount($transactionId)
    {
        try{
            $refundAmount = $this->where('transaction_id', $transactionId)
                ->sum('refund_amount');
            if($refundAmount){
                return $refundAmount;
            }

            return 0;
        }catch (QueryException $ex){
            return 0;
        }
    }

    public function addRefundDetails($refundId, $transactionId, $merchantId, $refundAmount, $currency, $refundType, $pg_name, $meta_id, $remark)
    {
        try {
            $this->refund_id         = $refundId;
            $this->transaction_id    = $transactionId;
            $this->merchant_id       = $merchantId;
            $this->refund_amount     = $refundAmount;
            $this->refund_currency   = $currency;
            $this->refund_type       = $refundType;
            $this->response_message  = "Refund Success";
            $this->refund_status     = RefundInternalStatus::SUCCESS;
            $this->internal_status   = RefundInternalStatus::SUCCESS;
            $this->processed_by      = "Support";
            $this->refund_reason     = $remark;
            $this->pg_name           = $pg_name;
            $this->meta_id           = $meta_id;
            $this->ref_date           = Carbon::now()->toDateString();
            if($this->save()){
                return true;
            }
            return false;

        }
        catch (QueryException $ex){
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
}
