<?php

namespace App\Models\PaymentManual;

use App\Models\Management\MerchantDetails;
use App\Models\Management\Payout;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class PayoutManualRecon extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_manual_recon';
    protected $primaryKey = 'payout_id';
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

    public function getPayoutManualRecon($filterData, $pageNo, $limit) {
        try {

            $data = $this->newQuery();

            if(isset($filterData)) {
                if(isset($filterData['payout_id']) && !empty($filterData['payout_id'])) {
                    $data->where('payout_id', $filterData['payout_id']);
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id'])) {
                    $data->where('merchant_id', $filterData['merchant_id']);
                }
                if(isset($filterData['is_solved']) && strcmp(strtolower($filterData['is_solved']), "all") !== 0) {
                    $data->where('is_solved', $filterData['is_solved']);
                }
                if(isset($filterData['manual_pay_batch_id']) && !empty($filterData['manual_pay_batch_id'])) {
                    $data->where('manual_pay_batch_id', $filterData['manual_pay_batch_id']);
                }
                if(isset($filterData['start_date']) && isset($filterData['end_date'])) {
                    $data->whereBetween("created_at", [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $data->with('payoutDetails');
            $data->with('merchantDetail')->orderBy('created_at', 'desc');
            if($data->count() > 0){
                return $data->paginate($limit);
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
    public function showAddedUtr($fileName)
    {
        try {
            $data=$this->where('file_name',$fileName)->get();
            if($data->count() > 0){
                return $data;
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

    public function getAmount($filterData, &$totalAmount, &$totalReleased, &$totalUnReleased)
    {
        try {
            $data = $this->newQuery();
            if(isset($filterData)) {
                if(isset($filterData['start_date']) && isset($filterData['end_date'])) {
                    $data->whereBetween("created_at", [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            $totalAmount = $data->sum('payout_amount');
            $totalReleased = $data->where('is_solved', true)->sum('payout_amount');
            $totalUnReleased = $data->where('is_solved', false)->sum('payout_amount');

        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            $totalAmount = 0;
            $totalReleased = 0;
            $totalUnReleased = 0;
        }
    }

    public function payoutDetails()
    {
        return $this->belongsTo(Payout::class, "payout_id", "payout_id");
    }

    public function merchantDetail()
    {
        return $this->belongsTo(MerchantDetails::class, "merchant_id", "merchant_id");
    }

    public function getCountByBatchId($batchId)
    {
        try {
            $result = $this->where('manual_pay_batch_id',$batchId)->count();
            if($result){
                return $result;
            }
            return 0;
        }catch (QueryException $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return 0;
        }
    }

}
