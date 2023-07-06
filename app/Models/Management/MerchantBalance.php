<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class MerchantBalance extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_balance';
    protected $primaryKey = 'merchant_id';
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

    public function getStatement($merchantId, $filterData, $limit, $pageNo)
    {
        try {
            $statement = $this->newQuery();
            $statement->where("merchant_id", $merchantId);

            if(isset($filterData)) {
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $statement->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            $statement->select([
                'pay_date',
                'open_balance',
                'payin',
                'payout',
                'refund',
                'un_settled',
                'settled',
                'closing_balance',
                'created_at',
                'updated_at',
            ]);
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $statement->orderBy('created_at', 'desc');
            if($statement->count() > 0){
                return $statement->paginate($limit);
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

    public function getMerchantBalanceSummary($startDate)
    {
        try {
            $merchantBalance = $this->where("pay_date", $startDate)
                ->select([
                    DB::raw("SUM(payin_without_fees) as total_collection"),
                    DB::raw("SUM(payout_without_fees) as total_payout"),
                    DB::raw("SUM(closing_balance) as total_payout_balance"),
                ])->first();
            if(isset($merchantBalance)) {
                return $merchantBalance;
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

    public function getMerchantBalanceChartSummary($startDate, $endDate)
    {
        try {
            $merchantBalance = $this->where("pay_date", ">=", $startDate)
                ->where("pay_date", "<=", $endDate)
                ->select([
                    DB::raw("SUM(payin_without_fees) as total_collection"),
                    DB::raw("SUM(payout_without_fees) as total_payout"),
                    "pay_date",
                    "merchant_id"
                ])
                ->groupBy("pay_date", "merchant_id")
                ->get()->toArray();
            if(sizeof($merchantBalance) > 0) {
                return $merchantBalance;
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

    public function getMerchantBalance($startDate, $endDate)
    {
        try {
            $merchantBalance = $this->with("merchantDetails")
                ->where("pay_date", ">=", $startDate)
                ->where("pay_date", "<=", $endDate)
                ->select([
                    DB::raw("SUM(payin_without_fees) as payin"),
                    DB::raw("SUM(payout_without_fees) as payout"),
                    DB::raw("SUM(refund) as refund"),
                    "merchant_id",
                ])
                ->groupBy("merchant_id")
                ->orderBy("payin", "desc")
                ->get();
            if($merchantBalance->count() > 0) {
                return $merchantBalance;
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

    public function getMerchantBalanceByMid($merchantId)
    {
        try {
            $merchantBalance = $this->with("merchantDetails")
                ->where("merchant_id", $merchantId)
                ->select([
                    DB::raw("SUM(un_settled) as un_settled_balance"),
                    "merchant_id",
                ])
                ->groupBy("merchant_id")
                ->orderBy("pay_date", "desc")
                ->first();
            if(isset($merchantBalance)) {
                return $merchantBalance;
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
    public function getMerchantUnsettledByMid($merchantId)
    {
        try {
            $merchantBalance = $this->with("merchantDetails")
                ->where("merchant_id", $merchantId)
                ->where('un_settled','>',0)
                ->orderBy("pay_date")
                ->get();
            if(isset($merchantBalance)) {
                return $merchantBalance;
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

    public function getMerchantLastClosingBalance($merchantId) {
        try {
            return $this->where("merchant_id", $merchantId)
                ->orderBy("pay_date", "desc")
                ->value("closing_balance");
        } catch (QueryException $ex) {
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

    public function getMerchantUnSettledBalance($merchantId) {
        try {
            return $this->where("merchant_id", $merchantId)
                ->sum("un_settled");
        } catch (QueryException $ex) {
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

    public function merchantDetails()
    {
        return $this->belongsTo(MerchantDetails::class, "merchant_id", "merchant_id");
    }

    public function getMerchantReleaseByMidAndData($merchantId,  $pay_date, $amount)
    {
        try {
            $this->where("merchant_id", $merchantId)->where('pay_date',$pay_date)->update([
                'amount_pre_settle'=>$amount
            ]);
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

    public function getMerchantBalancesByMid($merchantId)
    {
        try {
            $merchantBalance = $this->with("merchantDetails")
                ->where("merchant_id", $merchantId)
                ->where('un_settled','>',0)
                ->orderBy("pay_date", "desc")
                ->get(['merchant_id','pay_date','un_settled','settled','closing_balance']);
            if(isset($merchantBalance)) {
                return $merchantBalance;
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
