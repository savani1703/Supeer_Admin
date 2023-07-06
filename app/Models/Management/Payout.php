<?php

namespace App\Models\Management;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\DownloadLimit;
use App\Classes\Util\PayoutUtils;
use App\Classes\Util\PgName;
use App\Classes\Util\PgType;
use App\Classes\Util\TransactionUtils;
use App\Constant\PaymentStatus;
use App\Constant\PayoutStatus;
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
class Payout extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout';
    protected $primaryKey = 'payout_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_sync' => 'boolean'
    ];

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
        "approved_at_ist",
        "success_at_ist",
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

    public function getApprovedAtIstAttribute() {
        $updatedAtOriginal = $this->approved_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }
    public function getSuccessAtIstAttribute() {
        $updatedAtOriginal = $this->success_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    public function getPayoutSummary($filterData, $pgType) {
        try {
            $payout = $this->newQuery();
            $payout = PayoutUtils::CommanFilter($payout, $filterData, $pgType);
            $payout->select([
                DB::raw("COUNT(*) as total_payout"),
                DB::raw("SUM(payout_amount) as payout_amount"),
                DB::raw("SUM(IF(payout_status!='Failed',payout_fees,0)) as total_payout_fees"),
                DB::raw("SUM(IF(payout_status!='Failed',associate_fees,0)) as total_associate_fees"),
                DB::raw("SUM(IF(payout_status!='Failed',total_amount,0)) as total_payout_amount"),
            ]);
            $result = $payout->first();
            if(isset($result)){
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

    public function getPayout($filterData, $pgType, $limit, $pageNo) {
        try {
            $payout = $this->newQuery();
            $payout->with(["merchantDetails"]);
            $payout = PayoutUtils::CommanFilter($payout, $filterData, $pgType);
            $payout->select(PayoutUtils::SelectById());
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $payout->orderBy('created_at', 'desc');
            if($payout->count() > 0){
                return $payout->paginate($limit);
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

    public function getpayoutById($payoutId, $selectColumns = null) {
        try {
            $payout = $this->newQuery();
            $payout->where("payout_id", $payoutId);
            if(isset($selectColumns)) {
                $payout->select($selectColumns);
            }
            $result = $payout->first();
            if(isset($result)) {
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

    public function markAsResendWebhook($payoutId)
    {
        try {
            $payout = $this->where("payout_id", $payoutId)->update(["is_webhook_called" => "0"]);
            if($payout) {
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


    public function resetLowBalPayoutToInitialize()
    {
        try {
            $payout = $this->where("payout_status", PayoutStatus::LOWBAL)
                ->whereNull('bank_rrn')
                ->update([
                    'payout_status' => PayoutStatus::INITIALIZED,
                    'internal_status' => null,
                    'status_call' => 0
                ]);
            if($payout) {
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

    public function cancelledInitializedPayout($payoutId)
    {
        try {
            $payout = $this->where("payout_id", $payoutId)
                ->update([
                    "pg_ref_id" =>null,
                    "bank_rrn" => null,
                    "payout_status" => PayoutStatus::FAILED,
                    "pg_response_msg" => "Payout Cancelled",
                    "internal_status" => PayoutStatus::CANCELLED
                ]);

            if($payout) {
                return true;
            }else
            {
                $payout = $this->where("payout_id", $payoutId)
                    ->where(function ($q) {
                        $q->where("payout_status", PayoutStatus::PENDING);
                    })
                    ->whereNull('pg_ref_id')
                    ->whereNull('bank_rrn')
                    ->update([
                        "payout_status" => PayoutStatus::FAILED,
                        "pg_response_msg" => "Payout Failed From PG",
                        "internal_status" => PayoutStatus::CANCELLED
                    ]);
                if($payout) {
                    return true;
                }
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

    public function merchantDetails()
    {
        return $this->belongsTo(MerchantDetails::class, "merchant_id", "merchant_id");
    }

    public function addManualPayout($payoutId, $merchantId, $payoutAmount, $payoutFees, $payoutAssociateFees, $bankHolder, $accountNumber, $ifscCode, $bankRrn, $remarks)
    {
        try {
            $this->payout_id = $payoutId;
            $this->merchant_ref_id = DigiPayUtil::generateRandomString(16);
            $this->merchant_id = $merchantId;
            $this->payout_amount = $payoutAmount;
            $this->payout_fees = $payoutFees;
            $this->associate_fees = $payoutAssociateFees;
            $this->total_amount = floatval($payoutAmount) + floatval($payoutFees) + floatval($payoutAssociateFees);
            $this->payout_currency = "INR";
            $this->payout_type = "Manual";
            $this->account_holder_name = $bankHolder;
            $this->customer_name = "Manual" ;
            $this->customer_email = "Manual" ;
            $this->customer_mobile = "Manual" ;
            $this->is_webhook_called = 2 ;
            $this->status_call = 2 ;
            $this->bank_account = $accountNumber;
            $this->ifsc_code = $ifscCode;
            $this->bank_rrn = $bankRrn;
            $this->pg_response_msg = $remarks;
            $this->payout_status = PayoutStatus::SUCCESS;
            $this->payout_by = "INTERNAL_PANEL";
            $this->pxn_date = Carbon::now()->toDateString();

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

    public function getPayoutForRecon($payoutId)
    {
        try {
            $payout = $this->where("payout_id", $payoutId)->first();
            if(isset($payout)){
                return $payout;
            }
            return null;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }

    public function getPayoutDetailsForReport($filterData, $count = true, $offset = null)
    {
        try {
            $payout = $this->newQuery();
            $payout = PayoutUtils::CommanFilter($payout, $filterData, $filterData["pg_type"]);

            if($count === true){
                $result = $payout->count();
            }else{
                $result = $payout->with(["merchantDetails"])->offset($offset)->limit(DownloadLimit::LIMIT)->orderBy("created_at", "desc")->get();
            }
            if($result){
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

    public function getPayoutRecordForManualPayout($logicAmount, $logicKey, $merchantId, $sheetKey, $sheetValue)
    {
        try {

            $sql = $this->where('merchant_id', $merchantId)->where("payout_status", PayoutStatus::INITIALIZED);

            if(strcmp($logicKey,'greater_than') === 0){
                $sql->where('payout_amount','>',$logicAmount);
            }
            if(strcmp($logicKey,'less_than') === 0){
                $sql->where('payout_amount','<',$logicAmount);
            }
            if(strcmp($logicKey,'equal') === 0){
                $sql->where('payout_amount', $logicAmount);
            }
            if(strcmp($sheetKey,'count') === 0){
                $data = $sql->limit($sheetValue)->get();
            }else{
                $data = $sql->get();
            }
            if($data->count()) {
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

    public function getPayoutRecordForManualPayoutByLevelFlow($logicAmount, $logicKey, $merchantId, $sheetKey, $sheetValue, $eligibleCustomerIds)
    {
        try {

            $sql = $this->where('merchant_id', $merchantId)->whereIn('customer_id', $eligibleCustomerIds)->where("payout_status", PayoutStatus::INITIALIZED);

            if(strcmp($logicKey,'greater_than') === 0){
                $sql->where('payout_amount','>',$logicAmount);
            }
            if(strcmp($logicKey,'less_than') === 0){
                $sql->where('payout_amount','<',$logicAmount);
            }
            if(strcmp($logicKey,'equal') === 0){
                $sql->where('payout_amount', $logicAmount);
            }
            if(strcmp($sheetKey,'count') === 0){
                $data = $sql->limit($sheetValue)->get();
            }else{
                $data = $sql->get();
            }
            if($data->count()) {
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

    public function markAsProcessingForManualPayout($payoutId, $batchId, $debitAccount, $metaId, $pgName)
    {
        try {
            $this->where("payout_id", $payoutId)->update([
                "manual_pay_batch_id" => $batchId,
                "meta_merchant_id" => $debitAccount,
                "meta_id" => $metaId,
                "pg_name" => $pgName,
                "pg_type" => PgType::MANUAL,
                "payout_status" => PayoutStatus::PROCESSING,
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

    public function unMarkBatchPayout($batchId)
    {
        try {
            $this->where("manual_pay_batch_id", $batchId)->update([
                "manual_pay_batch_id" => null,
                "meta_merchant_id" => null,
                "meta_id" => null,
                "pg_name" => null,
                "pg_type" => PgType::AUTO,
                "payout_status" => PayoutStatus::INITIALIZED,
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

    public function getBatchCountAndSum($batchId)
    {
        try {
            $data = $this->where("manual_pay_batch_id", $batchId)
                ->where("payout_status", PayoutStatus::PROCESSING)
                ->where("pg_type", PgType::MANUAL)
                ->select(DB::raw("COUNT(*) as total_batch_record"), DB::raw("SUM(payout_amount) as total_batch_amount"))
                ->first();
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

    public function getPayoutForBatchTransfer($batchId)
    {
        try {
            $data = $this->where("manual_pay_batch_id", $batchId)->where("payout_status", PayoutStatus::PROCESSING)->get();
            if($data->count()) {
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

    public function markAsPendingBatchPayout($batchId)
    {
        try {
            if($this->where("manual_pay_batch_id", $batchId)
                ->where("payout_status", PayoutStatus::PROCESSING)
                ->update([
                    "payout_status" => PayoutStatus::PENDING
                ])) {
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

    public function getPayoutDetailsForBatchTransferStatusUpdate($payoutId)
    {
        try {
            $data = $this->where("payout_id", $payoutId)
                ->where(function ($q) {
                    $q->where("payout_status", PayoutStatus::PENDING);
                    $q->orWhere("payout_status", PayoutStatus::FAILED);
                })
                ->whereNull("bank_rrn")
                ->first();
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

    public function updatePayoutStatusForBatchTransferStatusUpdate($payoutId, $updateData)
    {
        try {
            $data = $this->where("payout_id", $payoutId)
                ->where(function ($q) {
                    $q->where("payout_status", PayoutStatus::PENDING);
                    $q->orWhere("payout_status", PayoutStatus::FAILED);
                })
                ->whereNull("bank_rrn")
                ->update($updateData);
            return $data;
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

    public function getPayoutDetailById($payoutId)
    {
        try {
            $data = $this->where("payout_id", $payoutId)->first();
            if(isset($data) && !empty($data)) {
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

    public function getMerchantSummary($merchantId)
    {
        try {
            $data = $this->where("merchant_id", $merchantId)
                ->where("payout_status", "<>", PayoutStatus::FAILED)
                ->select(
                    DB::raw("SUM(payout_amount) as withdrawal_amount"),
                    DB::raw("SUM(payout_fees + associate_fees) as withdrawal_fees")
                )
                ->first();
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

    public function getInitPayoutAmount()
    {
        try {
            return $this->where(function ($q) {
                $q->where("payout_status", PayoutStatus::INITIALIZED);
                $q->orWhere("payout_status", PayoutStatus::LOWBAL);
            })->sum("payout_amount");
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

    public function getPayoutSummaryByBank($bank_name)
    {
        try {
            return $this->where("bank_name", $bank_name)
                ->where(function ($q) {
                    $q->where("payout_status", PayoutStatus::INITIALIZED);
                    $q->orWhere("payout_status", PayoutStatus::LOWBAL);
                })
                ->select(
                    DB::raw("COUNT(*) as total_count"),
                    DB::raw("SUM(payout_amount) as total_amount"),
                )
                ->first();
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

    public function getPayoutSummaryForPgSummary($startDate, $endDate)
    {
        try {
            $payout = $this->where("payout_status", PayoutStatus::SUCCESS)
                ->whereBetween("created_at", [$startDate, $endDate])
                ->whereNotNull("meta_id")
                ->select([
                    DB::raw("SUM(IF(pg_type='MANUAL',payout_amount,0)) as total_manual_withdrawal"),
                    DB::raw("SUM(IF(pg_type='AUTO',payout_amount,0)) as total_auto_withdrawal"),
                    "pg_name",
                    "meta_id",
                    "pg_type",
                ])
                ->groupBy("meta_id")
                ->orderBy("pg_name", "desc")
                ->get();
            if($payout->count() > 0){
                return $payout;
            }
            return null;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }

    public function ResetInitializedPayout($payoutId)
    {
        try {
            $payout = $this->where("payout_id", $payoutId)
                ->where("payout_status", PayoutStatus::PENDING)
                ->whereNull('pg_ref_id')
                ->whereNull('bank_rrn')
                ->update([
                    "payout_status" => PayoutStatus::INITIALIZED,
                    "pg_response_msg" =>null,
                    "internal_status" => null,
                    "status_call" => 0
                ]);
            if($payout) {
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

    public function getSummary()
    {
        try {
            $merchantBalance = $this->where('payout_status',PaymentStatus::INITIALIZED)->groupBy('payout_status')
                ->select([
                    DB::raw("SUM(payout_amount) as total_payout_amount,count(1) as total_pending")
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

    public function LastSuccessDate()
    {
        try {
            $res = $this->where('payout_status',PayoutStatus::SUCCESS)->orderBy('success_at',"DESC")->first();
            if(isset($res)) {
                return $res->success_at_ist;
            }
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
        return Carbon::now()->toDateTimeString();
    }

    public function getLowBalCount()
    {
        try {
            $res = $this->where('payout_status',PayoutStatus::LOWBAL)->count();
           return $res;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
        return 0;
    }

    public function getPayoutSummaryForStatusTotalSummary($startDate,$endDate)
    {
        try {
            return $this
                ->where('created_at' ,'>=',$startDate)
                ->where('created_at' ,'<=',$endDate)
                ->count();
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);

        }
        return 0;
    }

    public function getPayoutSummaryForStatusSummary($startDate,$endDate)
    {
        try {
            $transaction = $this
                ->where('created_at' ,'>=',$startDate)
                ->where('created_at' ,'<=',$endDate)
                ->select([
                    'payout_status',
                    DB::raw("count(1) as txn_count"),
                    DB::raw("SUM(payout_amount) as total_amount")
                ])
                ->groupBy("payout_status")
                ->orderBy("payout_status")
                ->get();
            if($transaction->count() > 0){
                return $transaction;
            }
            return null;
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }

    public function getPayoutByDate($merchant_id, $startDate,$endDate)
    {
        try {
            return $this->where('merchant_id',  $merchant_id)->where("payout_status", "!=", PayoutStatus::FAILED)
                ->where("payout_status", "!=", PayoutStatus::CANCELLED)->where('created_at' ,'>=',$startDate)
                ->where('created_at' ,'<=',$endDate)->sum('total_amount');
        } catch (QueryException $ex) {
            report($ex);
        }
        return 0;
    }

    public function getTodayTotalPayoutByMetaId($metaId)
    {
        try {
            $startDate = Carbon::now()->format('Y-m-d 00:00:00');
            $endDate = Carbon::now()->format('Y-m-d 23:59:59');
            $_start = Carbon::createFromFormat('Y-m-d H:i:s', $startDate, 'Asia/Kolkata');
            $_start->setTimezone('UTC');
            $_end = Carbon::createFromFormat('Y-m-d H:i:s', $endDate, 'Asia/Kolkata');
            $_end->setTimezone('UTC');

            $result = $this->where('meta_id', $metaId)->whereBetween("created_at", [$_start, $_end])->count();
            if($result){
                return $result;
            }
            return 0;
        }catch (QueryException $ex){
            report($ex);
            return 0;
        }
    }


    public function getPayoutStatue($batchId)
    {
        try {
            $result = $this->select(['manual_pay_batch_id',
                DB::raw('COUNT(CASE payout_status WHEN "Success" THEN 1 ELSE NULL END) AS total_success_payout'),
                DB::raw('COUNT(CASE payout_status WHEN "Failed" THEN 1 ELSE NULL END) AS total_Failed_payout'),
                DB::raw('COUNT(CASE payout_status WHEN "Initialized" THEN 1 ELSE NULL END) AS total_Initialized_payout'),
                DB::raw('COUNT(CASE payout_status WHEN "Pending" THEN 1 ELSE NULL END) AS total_Pending_payout'),
                DB::raw('COUNT(CASE payout_status WHEN "Processing" THEN 1 ELSE NULL END) AS total_Processing_payout'),
            ])->where('manual_pay_batch_id',$batchId)->first();
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

    public function checkTempUtr($referenceNumber)
    {
        try {
            $result = $this->where('temp_bank_rrn', $referenceNumber)->exists();
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
            return true;
        }
    }

    public function setIDFCPayoutPgRefId($payoutId, $referenceNumber)
    {
        try {
            $result = $this->where('payout_id', $payoutId)
                ->where('payout_status', PayoutStatus::PENDING)
                ->whereNull('bank_rrn')
                ->update(['payout_status' => PayoutStatus::SUCCESS,'temp_bank_rrn' => $referenceNumber,'bank_rrn' => $referenceNumber,'pg_ref_id' => $referenceNumber,'success_at' => Carbon::now()]);
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

    public function setIDFCPayoutPgRefIdForReturn($payoutId, $referenceNumber)
    {
        try {
            $result = $this->where('payout_id', $payoutId)
                ->where('payout_status', PayoutStatus::PENDING)
                ->where('pg_name', PgName::IDFC)
                ->whereNull('bank_rrn')
                ->update(['temp_bank_rrn' => $referenceNumber,'bank_rrn' => $referenceNumber,'pg_ref_id' => $referenceNumber]);
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

    public function checkTempUtrIsExist($tempUtr)
    {
        try {
            if ($this->where("temp_bank_rrn", $tempUtr)->exists()) {
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

    public function getTotalPayoutListByIdForIDFC($batchId)
    {
        try {
            $result = $this->where("manual_pay_batch_id", $batchId)->where("payout_status", PayoutStatus::PENDING)->get();
            if($result){
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

    public function markAsSuccessWithTempUtrForIDFC($batchId, $payoutId, $tempUtr)
    {
        try {
            $result = $this->where("manual_pay_batch_id", $batchId)->where("payout_id", $payoutId)->where("payout_status", PayoutStatus::PENDING)->update(['payout_status' => PayoutStatus::SUCCESS,'bank_rrn' => $tempUtr]);
            if($result){
                return $result;
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

    public function payoutStatusUpdate($payoutId, $payoutStatus, $payoutUtr, $emailId)
    {
        try {
            $result = $this->where('payout_id',$payoutId)->where('payout_status', '!=',PayoutStatus::FAILED)->update(['payout_status' => $payoutStatus,'bank_rrn' => $payoutUtr,'process_by' => $emailId]);
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

    public function getTotalInitAmountPayoutById($merchantId)
    {
        try {
            $result = $this->where('merchant_id', $merchantId)->where('payout_status', PayoutStatus::INITIALIZED)->sum('payout_amount');
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

    public function getTotalInitCountPayoutById($merchantId)
    {
        try {
            $result = $this->where('merchant_id', $merchantId)->where('payout_status', PayoutStatus::INITIALIZED)->count();
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

    public function getLogicalInitAmountPayoutById($merchantId, $logicAmount, $loginKey)
    {
        try {
            $obj = $this->where('merchant_id', $merchantId)->where('payout_status', PayoutStatus::INITIALIZED);
            if(strcmp($loginKey,'greater_than') === 0){
                $obj->where('payout_amount','>',$logicAmount);
            }
                if(strcmp($loginKey,'less_than') === 0){
                $obj->where('payout_amount','<',$logicAmount);
            }
            if(strcmp($loginKey,'equal') === 0){
                $obj->where('payout_amount', $logicAmount);
            }
            $result = $obj->sum('payout_amount');
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

    public function getLogicalInitPayoutCountById($merchantId, $logicAmount, $loginKey)
    {
        try {
            $obj = $this->where('merchant_id', $merchantId)->where('payout_status', PayoutStatus::INITIALIZED);
            if(strcmp($loginKey,'greater_than') === 0){
                $obj->where('payout_amount','>',$logicAmount);
            }
                if(strcmp($loginKey,'less_than') === 0){
                $obj->where('payout_amount','<',$logicAmount);
            }
            if(strcmp($loginKey,'equal') === 0){
                $obj->where('payout_amount', $logicAmount);
            }
            $result = $obj->count();
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

    public function getPayoutForBatchTransferForCustom($array)
    {
        try {
            $data = $this->whereIn("payout_id", $array)->get();
            if($data->count()) {
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

    public function updateManualPayoutMetaDetails($batchId, $debitAccount, $metaId, $pgName, $updateBatchId)
    {
        try {
            $data = $this->whereIn("payout_id", $batchId)->update([
                "manual_pay_batch_id" => $updateBatchId,
                "meta_merchant_id" => $debitAccount,
                "meta_id" => $metaId,
                "pg_name" => $pgName,
                "pg_type" => PgType::MANUAL,
                "bank_rrn" => null,
                "payout_status" => PayoutStatus::PENDING,
            ]);
            dd($data);
        } catch (QueryException $ex) {
            dd($ex->getMessage());
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }

    public function getInitPayoutWithClient(){
        try {
            $result = $this->where("payout_status", PayoutStatus::SUCCESS)->groupBy('merchant_id')->select([
                'merchant_id',
                DB::raw("COUNT(*) as total_initialized"),
                'created_at',
            ])->orderBy('created_at','ASC')->get();
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

    public function checkUtrExists($bankUtrNumber)
    {
        try {
            $result = $this->where('bank_rrn', $bankUtrNumber)->first();
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

    public function getMerchantByBatchId($batchId)
    {
        try {
            $result = $this->select('merchant_id')->where('manual_pay_batch_id', $batchId)->with(["merchantDetails" => function($query){
                $query->select("merchant_id","merchant_name");
            }])->groupBy('merchant_id')->get();
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

    public function checkTempUtrExists($bankUtrNumber)
    {
        try {
            $result = $this->where('temp_bank_rrn', $bankUtrNumber)->first();
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

    public function getPayoutByPgRef($pgRef)
    {
        try {
            $result = $this->where('pg_ref_id', $pgRef)->first();
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

    public function getSafePayoutList($metaId)
    {
        try {
            $result = $this->where('meta_id', $metaId)->where('payout_status', PayoutStatus::PENDING)->whereNull('bank_rrn')->whereNull('temp_bank_rrn')->get();
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

    public function getPayoutDetailForLevel()
    {
        try {
            $result = $this->where('is_sync', false)->where('payout_status', PayoutStatus::SUCCESS)->whereNotNull('customer_id')->where(function ($query) {
                $query->where('merchant_id', 'MID_3UOP4XZR4OO17D')
                    ->orWhere('merchant_id','MID_2TYKNS2KMZ25RZ');
            })->limit(100)->orderBy('created_at','DESC')->get();
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

    public function getLastIdfcPayout($customerId, $accountNumber)
    {
        try {
            $result = $this->where('customer_id', $customerId)->where('bank_account', $accountNumber)->where('pg_name', "IDFC")->where('payout_status', PayoutStatus::SUCCESS)->orderBy('created_at','DESC')->first();
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

    public function getLastSuccessRec($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)->where('payout_status', PayoutStatus::SUCCESS)->orderBy('created_at','DESC')->first();
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

    public function markAsSync($customerId, $accountNumber)
    {
        try {
            $result = $this->where('customer_id', $customerId)->where('bank_account', $accountNumber)->update(['is_sync' => true]);
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

    public function getPayoutLoadOnlyFp()
    {
        try {

            $startDate = Carbon::now()->format('Y-m-d 00:00:00');
            $endDate = Carbon::now()->format('Y-m-d 23:59:59');
            $_start = Carbon::createFromFormat('Y-m-d H:i:s', $startDate, 'Asia/Kolkata');
            $_start->setTimezone('UTC');
            $_end = Carbon::createFromFormat('Y-m-d H:i:s', $endDate, 'Asia/Kolkata');
            $_end->setTimezone('UTC');

            $result = $this->where('payout_status', PayoutStatus::INITIALIZED)->whereNotNull('customer_id')->where(function ($query) {
                $query->where('merchant_id', 'MID_3UOP4XZR4OO17D')
                    ->orWhere('merchant_id','MID_2TYKNS2KMZ25RZ');
            })->orderBy('created_at','DESC')/*->whereBetween("created_at", [$_start, $_end])*/->get(['customer_id','payout_amount']);
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

    public function getPayoutCustomerIdByMid($merchantId)
    {
        try {
            $result = $this->where('merchant_id',$merchantId)->where('payout_status', PayoutStatus::INITIALIZED)->whereNotNull('customer_id')->pluck('customer_id')->toArray();
            if(count($result) > 0){
                return $result;
            }
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

    public function checkPayoutEligibleForYes($payoutId)
    {
        try {
            $result = $this->where("payout_id", $payoutId)->where('payout_status', PayoutStatus::PENDING)->whereNull('bank_rrn')->exists();
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

    public function markPayoutAsSuccssForYes($payoutId, $referenceNumber)
    {
        try {
            $result = $this->where('payout_id', $payoutId)
                ->where('payout_status', PayoutStatus::PENDING)
                ->whereNull('bank_rrn')
                ->update(['payout_status' => PayoutStatus::SUCCESS,'temp_bank_rrn' => $referenceNumber,'bank_rrn' => $referenceNumber,'pg_ref_id' => $referenceNumber,'success_at' => Carbon::now()]);
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

    public function markPayoutAsFailedForYes($payoutId, $status = null)
    {
        try {
            $result = $this->where('payout_id', $payoutId)
                ->where('payout_status', PayoutStatus::PENDING)
                ->whereNull('bank_rrn')
                ->update(['payout_status' => PayoutStatus::FAILED,'bank_rrn' => null,'pg_ref_id' => null,'pg_response_msg' => $status]);
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
