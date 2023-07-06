<?php

namespace App\Models\Management;


use App\Classes\Util\DownloadLimit;
use App\Classes\Util\PgName;
use App\Classes\Util\TransactionUtils;
use App\Constant\PaymentStatus;
use App\Constant\RefundStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class Transactions extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_transaction';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
        "success_at_ist",
        'last_success_at_ist_mindiff'
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

    public function getSuccessAtIstAttribute() {
        $updatedAtOriginal = $this->success_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }
    public function getLastSuccessAtIstMindiffAttribute() {
        $updatedAtOriginal = $this->success_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::now()->diffInMinutes(Carbon::parse($updatedAtOriginal, "UTC"));
        }
        return 20;
    }

    public function getPayableAmountAttribute() {
        $payableAmountOriginal = $this->attributes['payable_amount'];
        if(isset($payableAmountOriginal)) {
            $paymentStatus = $this->getAttributes()['payment_status'];
            if(isset($paymentStatus)) {
                if(strcmp($paymentStatus, PaymentStatus::SUCCESS) === 0) {
                    return $payableAmountOriginal ?? 0;
                }
            }
        }
        return 0;
    }

    public function getTransactions($filterData, $pgTye, $limit, $pageNo) {
        try {

            $transactions = TransactionUtils::CommanFilter($this->newQuery(), $filterData, $pgTye);
            $transactions->with(["merchantDetails"]);
            $transactions->select(TransactionUtils::SelectById());
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $transactions->orderBy('created_at', 'desc');
            if($transactions->count() > 0){
                return $transactions->paginate($limit);
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

    public function getTransactionById($transactionId, $selectColumns = null) {
        try {
            $transaction = $this->newQuery();
            $transaction->where("transaction_id", $transactionId);
            if(isset($selectColumns)) {
                $transaction->select($selectColumns);
            }
            $result = $transaction->first();
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

    public function getTransactionSummary($filterData, $pgTye) {
        try {
            $transactions = $this->newQuery();
            $transactions = TransactionUtils::CommanFilter($transactions, $filterData, $pgTye);
            $transactions->select([
                DB::raw("COUNT(*) as total_txn"),
                DB::raw("SUM(payment_amount) as total_payment_amount"),
                DB::raw("SUM(IF(payment_status='Success',pg_fees,0)) as total_pg_fees"),
                DB::raw("SUM(IF(payment_status='Success',associate_fees,0)) as total_associate_fees"),
                DB::raw("SUM(IF(payment_status='Success',payable_amount,0)) as total_payable_amount"),
            ]);
            $result = $transactions->first();
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

    public function resendTransactionWebhook($transactionId) {
        try {
            return $this->where("transaction_id", $transactionId)
                ->update([
                    'is_webhook_call' => '0'
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

    public function updateTransactionDataById($transactionId, $updateColumn, $updateValue) {
        try {
            return $this->where("transaction_id", $transactionId)
                ->update([
                    $updateColumn => $updateValue
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

    public function updateTransactionTempUtr($transactionId, $tempUtr)
    {
        try {
            return $this->where("transaction_id", $transactionId)
                ->where("payment_status", "!=", PaymentStatus::SUCCESS)
                ->update([
                    "temp_bank_utr" => $tempUtr
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

    public function getTransactionAmountForRefund($transactionId) {
        try {
            $transaction = $this->where('transaction_id', $transactionId)
                ->where(function ($query) {
                    $query->where('payment_status', PaymentStatus::SUCCESS)
                        ->orWhere('payment_status', RefundStatus::FULL_REFUND)
                        ->orWhere('payment_status', RefundStatus::PARTIAL_REFUND);
                })
                ->value("payment_amount");
            return $transaction;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return -1;
        }
    }

    public function getTransactionDetailsForReport($filterData, $count = true, $offset = null) {
        try {
            $transactions = TransactionUtils::CommanFilter($this->newQuery(), $filterData, $filterData['pg_type']);
            if($count === true){
                $result = $transactions->count();
            }else{
                $result = $transactions->with(["merchantDetails"])->offset($offset)->limit(DownloadLimit::LIMIT)->orderBy("created_at", "desc")->get();
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

    public function checkUtrIsUsed($utrRef)
    {
        try {
            return $this->where("bank_rrn", $utrRef)->exists();
        } catch (QueryException $ex) {
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

    public function merchantDetails()
    {
        return $this->belongsTo(MerchantDetails::class, "merchant_id", "merchant_id");
    }

    public function customerLevelDetails()
    {
        return $this->hasOne(CustomerLevel::class, "customer_id", "customer_id");
    }

    public function checkTransactioIsExist($txn_id)
    {
        try {
            if ($this->where("transaction_id", $txn_id)->exists()) {
                return true;
            }
        } catch (QueryException $ex) {
        }
        return false;
    }

    public function addManualPayIn($merchantId, $transactionId, $paymentAmount, $paymentFees, $pgType, $pgRefId, $bankRrn, $merchantOrderId, $pgResMsg, $transactionDate)
    {
        try {
           $this->transaction_id = $transactionId;
           $this->txn_token = $merchantOrderId;
           $this->merchant_order_id = $merchantOrderId;
           $this->merchant_id = $merchantId;
           $this->customer_id = $merchantOrderId;
           $this->currency = "INR";
           $this->payment_amount = $paymentAmount;
           $this->pg_fees = $paymentFees;
           $this->payable_amount = floatval($paymentAmount - $paymentFees);
           $this->payment_status = PaymentStatus::SUCCESS;
           $this->txn_date = Carbon::parse($transactionDate)->setTimezone('Asia/Kolkata')->format('Y-m-d');
           $this->pg_res_msg = $pgResMsg;
           $this->pg_ref_id = $pgRefId;
           $this->pg_type = $pgType;
           $this->temp_bank_utr = $bankRrn;
           $this->bank_rrn = $bankRrn;
           $this->callback_url = " ";
           $this->is_webhook_call = 11;
           $this->created_at = $transactionDate;
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

    public function getTransactionForRecon($transactionId)
    {
        try {
            $transaction = $this->where("transaction_id", $transactionId)->first();
            if(isset($transaction)){
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

    public function getTransactionSummaryForPgSummary($startDate, $endDate)
    {
        try {
            $transaction = $this->where("payment_status", PaymentStatus::SUCCESS)
                ->whereBetween("created_at", [$startDate, $endDate])
                ->whereNotNull("meta_id")
                ->select([
                    DB::raw("SUM(IF(pg_type='MANUAL',payment_amount,0)) as total_manual_collection"),
                    DB::raw("SUM(IF(pg_type='AUTO',payment_amount,0)) as total_auto_collection"),
                    "pg_name",
                    "meta_id",
                    "pg_type",
                ])
                ->groupBy("meta_id")
                ->orderBy("pg_name", "desc")
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

    public function getTransactionDetailsForRefund($transactionId)
    {
        try {
            $result = $this->where('transaction_id', $transactionId)->first();
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

    public function getPgPayInSummary($merchantId, $startDate, $endDate)
    {
        try {
            $transaction = $this->newQuery();
            if(strcmp(strtolower($merchantId), "all") !== 0) {
                $transaction->where("merchant_id", $merchantId);
            }
            $transaction->whereBetween('txn_date', [$startDate, $endDate]);
            $transaction->whereNotNull("meta_id");
            $transaction->select([
                "meta_id",
                "pg_name",
                "meta_merchant_id",
                "payment_status",
                DB::raw("COUNT(*) as total_txn"),
            ]);
            $transaction->groupBy(["meta_id", "payment_status"]);
            $transaction->orderBy("pg_name", "asc");
            $result = $transaction->get();
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

    public function getLastTxnTimeByMetaId($metaId)
    {
        try {
            $data = $this->where("meta_id", $metaId)
                ->where("payment_status", PaymentStatus::SUCCESS)
                ->orderBy("created_at", "desc")
                ->first();
            if(isset($data)) {
                return $data->created_at_ist;
            }
            return null;
        } catch (QueryException $ex) {
            return null;
        }
    }

    public function LastSuccessDate()
    {
        try {
            $res = $this->where('payment_status',PaymentStatus::SUCCESS)->orderBy('success_at',"DESC")->first();
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

    public function getTransactionByUTR($bank_utr,  $selectColumns)
    {
        try {
            $transaction = $this->newQuery();
            $transaction->where("bank_rrn", $bank_utr);
            if(isset($selectColumns)) {
                $transaction->select($selectColumns);
            }
            $result = $transaction->first();
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

    public function checkCustomerisRisky($customer_id, $merchant_id)
    {
        try {
            $rec= $this->where('merchant_id', $merchant_id)->where('customer_id',$customer_id)->where('payment_status','!=' ,PaymentStatus::INITIALIZED)->orderBy('created_at','DESC')->limit(10)->get();
            return  $rec->count()==10 && $rec->where('payment_status', PaymentStatus::SUCCESS)->count()==0;
        } catch (QueryException $ex) {
            report($ex);
            return false;
        }
    }

    public function LastSuccessDateByMetaId($av_bank_id)
    {
        try {
            $res = $this->where('payment_status',PaymentStatus::SUCCESS)->where('meta_id',$av_bank_id)->orderBy('success_at',"DESC")->first();
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
        return null;
    }
    public function LastSuccessUTCDateByMetaId($av_bank_id)
    {
        try {
            $res = $this->where('payment_status',PaymentStatus::SUCCESS)->where('meta_id',$av_bank_id)->orderBy('success_at',"DESC")->first();
            if(isset($res)) {
                return $res->success_at;
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
        return null;
    }

    public function LastSuccessMinDiffDateByMetaId($av_bank_id)
    {
        try {
            $res = $this->where('payment_status',PaymentStatus::SUCCESS)->where('meta_id',$av_bank_id)->orderBy('success_at',"DESC")->first();
            if(isset($res)) {
                return $res->last_success_at_ist_mindiff;
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
        return null;
    }
    public function getTransactionSummaryForStatusSummary($startDate,$endDate,$merchantId)
    {
        try {
            $data=$this->newQuery();
            if(isset($merchantId) && !empty($merchantId) && strcmp(strtolower($merchantId), "all") !== 0) {
                $data->where('merchant_id', $merchantId);
            }
            $data
                ->whereBetween("txn_date", [$startDate, $endDate])
                 ->select([
                    'payment_status',
                    DB::raw("count(1) as txn_count"),
                    DB::raw("SUM(payment_amount) as total_amount")
                ])
                ->groupBy("payment_status")
                ->orderBy("payment_status");

            if($data->count() > 0){
                return $data->get();
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
    public function getTransactionSummaryForBlankDataSummary($startDate,$endDate,$merchantId)
    {
        try {
            $data=$this->newQuery();
            if(isset($merchantId) && !empty($merchantId) && strcmp(strtolower($merchantId), "all") !== 0) {
                $data->where('merchant_id', $merchantId);
            }

            $data->
                whereBetween("txn_date", [$startDate, $endDate])
                ->where("payment_status","Initialized")
                ->whereNotNull("showing_data")
                ->select([
                    'showing_data',
                    DB::raw("count(1) as txn_count"),
                    DB::raw("SUM(payment_amount) as total_amount")
                ])
                ->groupBy("showing_data")
                ->orderBy("showing_data");

            if($data->count() > 0){
                return $data ->get();
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
    public function getTransactionSummaryForStatusTotalSummary($startDate,$endDate,$merchantId)
    {
        try {
            $data=$this->newQuery();
            if(isset($merchantId) && !empty($merchantId) && strcmp(strtolower($merchantId), "all") !== 0) {
                $data->where('merchant_id', $merchantId);
            }

            return
                 $data->whereBetween("txn_date", [$startDate, $endDate])
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
    public function getStuckTransactions()
    {
        try {
            $res= $this
                ->where("created_at",'like','%18:30:00')
                ->get();
            if($res->count()>0)
            {
                return $res;
            }
        }catch (QueryException $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);

        }
        return null;
    }

    public function getTransactionForUpiAutoSuccess($merchantId, $customer_id, $amount)
    {
        try {
            $fromdate=\Illuminate\Support\Carbon::now()->subDays(5)->toDateTimeString();
            $result = $this->where('merchant_id', $merchantId)->where('payment_status', '!=', PaymentStatus::SUCCESS)->where('customer_id', $customer_id)->where('created_at','>',$fromdate)->where('payment_amount', $amount)->orderBy('created_at', 'DESC')->first();
            if (isset($result)) {
                return $result;
            }
        } catch (QueryException $ex) {
            report($ex);
        }
        return null;
    }

    public function getTransactionByUpiMobileAndAmount($merchantId, $mobile, $amount)
    {
        try {
            $fromdate=\Illuminate\Support\Carbon::now()->subDays(15)->toDateTimeString();
            $result = $this->where('merchant_id', $merchantId)->where(function ($query) use ($mobile) {
                $query->where('customer_mobile',$mobile);
                $query->orWhere('customer_mobile',"91".$mobile);
            })->where('payment_amount',$amount)->where('created_at', '>', $fromdate)->where('payment_status', '!=', PaymentStatus::SUCCESS)->orderBy('created_at', 'DESC')->first();
            if (isset($result)) {
                return $result;
            }
        } catch (QueryException $ex) {
            report($ex);
        }
        return null;
    }
    public function setTempBankUtrNumberForAutoSucess($transactionId, $utrNumber)
    {
        try {
            return $this->where('transaction_id', $transactionId)->where('payment_status', '<>', PaymentStatus::SUCCESS)->update(['temp_bank_utr' => $utrNumber]);
        } catch (QueryException $ex) {
            report($ex);
            return false;
        }
    }

    public function getTransactionByUpiAndDate($merchantId, $customer_id, $created_at)
    {
        try {
            $enddata = Carbon::parse($created_at)->addHours(5)->toDateTimeString();
            $startdate = Carbon::parse($created_at)->subHours(2)->toDateTimeString();
            $result = $this->where('merchant_id', $merchantId)->where('payment_status', '!=', PaymentStatus::SUCCESS)->where('customer_id', $customer_id)->where('created_at', '>', $startdate)->where('created_at', '<', $enddata)->orderBy('created_at', 'DESC')->first();
            if (isset($result)) {
                return $result;
            }
        } catch (QueryException $ex) {
            report($ex);
        }
        return null;
    }

    public function getTransactionByUpiAndWithoutDate($merchantId, $customer_id)
    {
        try {
            $fromdate=\Illuminate\Support\Carbon::now()->subDays(15)->toDateTimeString();
            $result = $this->where('merchant_id', $merchantId)->where('payment_status', '!=', PaymentStatus::SUCCESS)->where('customer_id', $customer_id)->where('created_at','>',$fromdate)->orderBy('created_at', 'DESC')->first();
            if (isset($result)) {
                return $result;
            }
        } catch (QueryException $ex) {
            report($ex);
        }
        return null;
    }

    public function getTransactionSearchWithTempUTR($merchantId, $payment_utr)
    {
        try {
            $fromdate=\Illuminate\Support\Carbon::now()->subDays(15)->toDateTimeString();
            $result = $this->where('merchant_id', $merchantId)->where('payment_status', '!=', PaymentStatus::SUCCESS)->where('temp_bank_utr', $payment_utr)->where('created_at', '>', $fromdate)->orderBy('created_at', 'DESC')->first();
            if (isset($result)) {
                return $result;
            }
        } catch (QueryException $ex) {
            report($ex);
        }
        return null;
    }

    public function DeleteManualTransaction($transaction_id, $payment_amount)
    {
        try {
            return $this->where('transaction_id', $transaction_id)->where('payment_amount',$payment_amount)->where('payment_status',  PaymentStatus::SUCCESS)->whereNull('meta_id')->whereNull('meta_merchant_id')->update([
                'payment_status'=>PaymentStatus::FAILED,
                'pg_res_msg'=>"Deleted From Panel"
            ]);
        } catch (QueryException $ex) {
            report($ex);
            return false;
        }
    }

    public function transactionRemoveFees($transaction_id, $payment_amount)
    {
        try {
            return $this->where('transaction_id', $transaction_id)->where('payment_amount',$payment_amount)->where('payment_status',  PaymentStatus::SUCCESS)->whereNull('meta_id')->whereNull('meta_merchant_id')->update([
                'pg_fees'=>0,
                'associate_fees'=>0,
                'payable_amount'=>$payment_amount,
            ]);
        } catch (QueryException $ex) {
            report($ex);
            return false;
        }
    }

    public function transactionMarkAsSuccessForUpi($txnInfo, $transactionId)
    {
        try {
            $result = $this->where('transaction_id', $transactionId)->whereNull('bank_rrn')->whereNotNull('temp_bank_utr')->where('payment_status', '!=', PaymentStatus::SUCCESS)->where('payment_status', '!=', PaymentStatus::FAILED)->update(
                [
                    'payment_status' => PaymentStatus::SUCCESS,
                    'success_at' => Carbon::now(),
                    'bank_rrn' => $txnInfo->payment_utr
                ]);
            if ($result) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            report($ex);
            return false;
        }
    }

    public function transactionMarkAsFailedForUpi($txnInfo, $transactionId)
    {
        try {
            $result = $this->where('transaction_id', $transactionId)->whereNotNull('temp_bank_utr')->whereNull('bank_rrn')->where('payment_status', '!=', PaymentStatus::SUCCESS)->where('payment_status', '!=', PaymentStatus::FAILED)->update(
                [
                    'payment_status' => PaymentStatus::FAILED,
                    'success_at' => Carbon::now(),
                    'bank_rrn' => "UTRReused",
                    'pg_res_msg' => "UTR Reused"
                ]);
            if ($result) {
                return true;
            }
            return false;
        } catch (\Exception $ex) {
            report($ex);
            return false;
        }
    }
    public function getPayinByDate($merchant_id,$startDate,$endDate)
    {
        try {
            return $this->where('merchant_id',  $merchant_id)->where('payment_status',  PaymentStatus::SUCCESS)->where('created_at' ,'>=',$startDate)
                ->where('created_at' ,'<=',$endDate)->sum('payable_amount');
        } catch (QueryException $ex) {
            report($ex);
        }
        return 0;
    }

    public function getAllHid($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)->groupBy('browser_id')->pluck('browser_id');
            if(count($result) > 0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function checkDeviceHasMultipleCustomer($browserId)
    {
        try {
            $result = $this->where('browser_id', $browserId)
                ->groupBy('customer_id')
                ->select('customer_id')
                ->get();
            if($result->count()){
                return $result->count();
            }
            return 1;
        }catch (QueryException $ex){
            report($ex);
            return 1;
        }
    }

    public function getTransactionByBrowserId($browserId)
    {
        try {
            $result = $this->where('browser_id', $browserId)
                ->groupBy('customer_id')
                ->with(["merchantDetails"])
                ->select('customer_id','merchant_id','browser_id','customer_email','customer_mobile')
                ->get();
            if($result->count()){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
    public function getTransactionByBrowserIdForBlock($browserId)
    {
        try {
            $result = $this->where('browser_id', $browserId)
                ->groupBy('customer_id')
                ->select('customer_id','merchant_id','browser_id')
                ->get();
            if($result->count()){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getAllCustomerEmailListById($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)
                ->whereNotNull('customer_email')
                ->groupBy('customer_email')
                ->get(['customer_email']);
             if($result->count()){
                 return $result;
             }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
    public function getAllCustomerMobileListById($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)
                ->whereNotNull('customer_mobile')
                ->groupBy('customer_mobile')
                ->get(['customer_mobile']);
            if($result->count()){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getAllCustomerUpiListById($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)
                ->whereNotNull('payment_data')
                ->groupBy('payment_data')
                ->get(['payment_data']);
            if($result->count()){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getTransactionLastSuccessDateById($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)->orderBy('success_at','DESC')->first(['success_at']);
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getTransactionByUTRForMap($bank_utr)
    {
        try {
            $result = $this->where("bank_rrn", $bank_utr)->first();
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

    public function getTransactionChartDataByHours( $startDate, $endDate ,$merchantId,$custLevel)
    {
        try {
            $data = $this->newQuery();
            if(isset($merchantId) && !empty($merchantId) && strcmp(strtolower($merchantId), "all") !== 0) {
                $data->where('tbl_transaction.merchant_id', $merchantId);
            }
            if(isset($custLevel) && !empty($custLevel) && strcmp(strtolower($custLevel), "all") !== 0) {
                $data->leftJoin("tbl_customer_level", "tbl_customer_level.customer_id", "=", "tbl_transaction.customer_id")->where('tbl_customer_level.user_security_level',$custLevel);
            }
            $data->where(function ($q) {
                $q->where("payment_status", \App\Classes\Util\PaymentStatus::INITIALIZED);
                $q->orWhere("payment_status", PaymentStatus::PENDING);
                $q->orWhere("payment_status", PaymentStatus::PROCESSING);
                $q->orWhere("payment_status", PaymentStatus::SUCCESS);
                $q->orWhere("payment_status", PaymentStatus::FAILED);
            });
            $data->whereBetween("tbl_transaction.created_at", [$startDate, $endDate]);

            $data->select(
                "tbl_transaction.payment_status","tbl_transaction.customer_id",
                DB::raw("DATE_FORMAT(CONVERT_TZ(tbl_transaction.created_at, '+00:00', '+05:30'), '%H') as chart_time"),
                DB::raw("count(*) chart_count")
            );

            $data->groupBy(DB::raw("DATE_FORMAT(CONVERT_TZ(tbl_transaction.created_at, '+00:00', '+05:30'), '%H')"), "payment_status");
            $data->orderBy("chart_time", "desc");
            $result = $data->get();
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
            return null;
        }
    }




    public function getCustomersStateDetailsById($customerId)
    {
        try {
            $result = $this->where('customer_id',$customerId)->groupBy('cust_state')->get(['cust_state']);
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

    /*public function getTransactionStateDetailsState($customerId, $customerState)
    {
        try {

            $result = $this->where('customer_id',$customerId)->where('cust_state',$customerState)
                ->select(
                    DB::raw("count(1) as total_transaction"),
                    DB::raw('count(case when payment_status = "Success" then 1 else 0 end) as total_success'),
                    DB::raw('count(case when payment_status = "Failed" then 1 else 0 end) as total_failed'),
                    DB::raw('count(case when payment_status = "Initialized" then 1 else 0 end) as total_initialized'),
                    DB::raw('count(case when payment_status = "Not Attempted" then 1 else 0 end) as total_not_attempted')
                )
                ->groupBy("payment_status")
                ->get();
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
    }*/

    public function getTransactionStateDetailsState($customerId, $customerState, $paymentStatus)
    {
        try {
            $result = $this->select([
                DB::raw("COUNT(*) as total_txn"),
                DB::raw("SUM(payment_amount) as txn_sum"),
            ])->where('customer_id',$customerId)->where('cust_state',$customerState)->where('payment_status',$paymentStatus)->first();
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

    public function getUserStateData($filterData)
    {
        try {
             $SData=$this->newQuery();
            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['customer_id']) && !empty($filterData['customer_id'])) {
                    $SData->where('customer_id', $filterData['customer_id']);
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id'])) {
                    $SData->where('merchant_id', $filterData['merchant_id']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $SData->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            $SData->select([
                'cust_state',
                DB::raw("COUNT(*) as total_txn"),
                DB::raw("SUM(IF(payment_status='Success',payment_amount,0)) as success_sum"),
                DB::raw("SUM(IF(payment_status='Failed',payment_amount,0)) as failed_sum"),
                DB::raw("SUM(IF(payment_status='Initialized',payment_amount,0)) as initialized_sum"),
                DB::raw("SUM(IF(payment_status='Pending',payment_amount,0)) as pending_sum"),
                DB::raw("SUM(IF(payment_status='Processing',payment_amount,0)) as processing_sum"),
                DB::raw('COUNT(CASE payment_status WHEN "Success" THEN 1 ELSE NULL END) AS total_success_txn'),
                DB::raw('COUNT(CASE payment_status WHEN "Failed" THEN 1 ELSE NULL END) AS total_Failed_txn'),
                DB::raw('COUNT(CASE payment_status WHEN "Initialized" THEN 1 ELSE NULL END) AS total_Initialized_txn'),
                DB::raw('COUNT(CASE payment_status WHEN "Pending" THEN 1 ELSE NULL END) AS total_Pending_txn'),
                DB::raw('COUNT(CASE payment_status WHEN "Processing" THEN 1 ELSE NULL END) AS total_Processing_txn'),
            ])->orderBy('total_txn','DESC')->groupBy('cust_state')->where('cust_state', '<>', '')->where('cust_country','IN');
            $result = $SData->get();
            if ($result->count() > 0) {
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
            return 0;
        }
    }
    public function LastState($merchant_id,$customerId)
    {
        try {
            $result = $this->where('merchant_id', $merchant_id)->where('customer_id', $customerId)->orderBy('created_at','DESC')->first(['cust_state']);
            if($result){
                return $result->cust_state;
            }
        }catch (QueryException $ex){
            report($ex);
        }
        return null;
    }

    public function getstateCust($state, $filterData)
    {
        try {
            $obj = $this->newQuery();
            $users = $obj
//                ->whereBetween('tbl_customer_level.created_at', [$filterData['start_date'], $filterData['end_date']])
                ->where('tbl_transaction.cust_state',$state)
                ->select('tbl_transaction.customer_id','tbl_customer_level.user_security_level',)
                ->distinct()
                ->rightJoin("tbl_customer_level", "tbl_customer_level.customer_id", "=", "tbl_transaction.customer_id")
                ->get();
            if($users){
                return $users;
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

    public function getMinTicketSize($merchant_id, $startDate,  $endDate)
    {
        try {
            $datetime=\Illuminate\Support\Carbon::now()->subHour()->toDateTimeString();
            $tiket_amount = $this
                ->where("merchant_id", "=", $merchant_id)
                ->where("created_at", ">=", $datetime)
                ->orderBy("payment_amount", "ASC")
                ->first(['payment_amount']);
            if(isset( $tiket_amount)) {
                return $tiket_amount->payment_amount;
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
        return 0;
    }

    public function getMaxTicketSize($merchant_id,string $startDate, string $endDate)
    {
        try {
            $datetime=\Illuminate\Support\Carbon::now()->subHour()->toDateTimeString();
            $tiket_amount = $this
                ->where("merchant_id", "=", $merchant_id)
                ->where("created_at", ">=", $datetime)
                ->orderBy("payment_amount", "DESC")
                ->first(['payment_amount']);
            if(isset( $tiket_amount)) {
                return $tiket_amount->payment_amount;
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
        return 0;
    }

    public function getCustBehaviour($filterData, $limit, $pageNo) {
        try {
            $data=$this->newQuery();
            if(isset($filterData['customer_id']) && !empty($filterData['customer_id'])) {
                $data->where('customer_id', $filterData['customer_id']);
            }
            $data->select([
                        'merchant_id','customer_id','payment_status',
                        DB::raw("COUNT(*) as count"),
                        DB::raw("SUM(payment_amount) as amount")
                ])
            ->where('customer_id', '<>', '')->where('meta_merchant_id', '<>', '')->whereRaw('created_at > DATE_SUB(now(), INTERVAL 30 DAY)')
                ->groupBy('merchant_id', 'customer_id','payment_status')->orderBy('merchant_id','asc');

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
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
}
