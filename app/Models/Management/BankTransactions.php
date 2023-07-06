<?php

namespace App\Models\Management;

use App\Classes\Util\BankTransactionData;
use App\Classes\Util\DownloadLimit;
use App\Models\PaymentManual\AvailableBank;
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
class BankTransactions extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_bank_transactions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        "isget" => "boolean"
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
    public function getUncalimedBal()
    {
        try {
              $res=  $this->where('isget',0)->sum('amount');
              if(isset($res))
              {
                  return  $res;
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

    public function getBankTransactions($filterData, $limit, $pageNo) {
        try {
            $bankTransactions = $this->newQuery();
            $bankTransactions->with("bankDetails");
            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['payment_utr']) && !empty($filterData['payment_utr'])) {
                    $bankTransactions->where('payment_utr', "like", "%".$filterData['payment_utr']);
                }
                if(isset($filterData['amount']) && !empty($filterData['amount'])) {
                    $bankTransactions->where('amount', $filterData['amount']);
                }
                if(isset($filterData['isget']) && strcmp($filterData['isget'], "ALL") !== 0) {
                    $bankTransactions->where('isget', $filterData['isget']);
                }
                if(isset($filterData['bank_account']) && strcmp($filterData['bank_account'], "All") !== 0) {
                    $bankTransactions->where('account_number', $filterData['bank_account']);
                }
                if(isset($filterData['bank_name']) && strcmp($filterData['bank_name'], "All") !== 0) {
                     if ($filterData['bank_name']=='FEDERAL'){
                         $filterData['bank_name']='FED';
                     }
                    $bankTransactions->where('bank_name', $filterData['bank_name']);
                }
                if(isset($filterData['upi_id'])) {
                    $bankTransactions->where('upi_id', $filterData['upi_id']);
                }
                if(isset($filterData['account_number'])) {
                    $bankTransactions->where('account_number', $filterData['account_number']);
                }
                if(isset($filterData['min_amount']) && !empty($filterData['min_amount'] && $filterData['min_amount'] > 0 ) && isset($filterData['max_amount']) && !empty($filterData['max_amount']) && $filterData['max_amount'] > 0 ) {
                    $bankTransactions->where('amount', '>=', $filterData['min_amount']);
                    $bankTransactions->where('amount', '<=', $filterData['max_amount']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $bankTransactions->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            $bankTransactions->select([
                "id","bank_txn_date", "upi_id","mobile_number","description", "amount", "payment_utr", "transaction_mode","isget","transaction_mode","bank_created_at","entry_date","payment_mode",
                "transaction_date", "account_number", "bank_name", "udf1", "udf2",
                "udf3", "udf4", "udf5", "created_at"
            ]);

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $bankTransactions->orderBy('created_at', 'desc');
            if($bankTransactions->count() > 0){
                return $bankTransactions->paginate($limit);
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

    public function isEligibleForTransactionTempUTRUpdate($tempUtr) {
        try {
            return $this->where("payment_utr", $tempUtr)->first();
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

    public function getBankTransactionDetailsForReport($filterData, $count = true, $offset = null) {
        try {
            $bankTransactions = $this->newQuery();
            $bankTransactions->with("bankDetails");
            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['payment_utr']) && !empty($filterData['payment_utr'])) {
                    $bankTransactions->where('payment_utr', "like", "%".$filterData['payment_utr']);
                }
                if(isset($filterData['amount']) && !empty($filterData['amount'])) {
                    $bankTransactions->where('amount', $filterData['amount']);
                }
                if(isset($filterData['isget']) && strcmp($filterData['isget'], "ALL") !== 0) {
                    $bankTransactions->where('isget', $filterData['isget']);
                }
                if(isset($filterData['account_number']) && !empty($filterData['account_number'])) {
                    $bankTransactions->where('account_number', $filterData['account_number']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $bankTransactions->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            if($count === true){
                $result = $bankTransactions->count();
            }else{
                $result = $bankTransactions->offset($offset)->limit(DownloadLimit::LIMIT)->orderBy("created_at", "desc")->get();
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

    public function bankDetails() {
        return $this->belongsTo(AvailableBank::class, "account_number", "account_number");
    }

    public function MarkAsUsed($payment_utr)
    {
        try {
            $bank = $this->where("payment_utr", $payment_utr)
                ->where("isget", false)
                ->update([
                    "isget" => 1,
                ]);
            if($bank) {
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

    public function getPIForAutoSuccess()
    {
        try {

            $startDate = Carbon::now("Asia/Kolkata")->format("Y-m-d 00:00:00");
            $endDate = Carbon::now("Asia/Kolkata");
            $startDatedt=   Carbon::parse($startDate, "Asia/Kolkata")->subDays(10)->setTimezone("UTC")->format("Y-m-d H:i:s");
            $endDate = Carbon::parse($endDate, "Asia/Kolkata")->subHours(5)->setTimezone("UTC")->format("Y-m-d H:i:s");
            $res = $this->where('account_number','<>','13040200031632')->where('account_number','<>','13040200031608')->where('isget', 0)->where('amount','>=',500)->whereNotNull('upi_id')->where('created_at','<',$endDate)->where('created_at','>',$startDatedt)->orderBy('created_at')->get();
            //  $res = $this->whereIn('payment_utr',['226326777463','226315914489','226344989351','226356640155','226372513887','226349568833','226347130471','226328332494','226370924794','226313018054','226328073160','226312943675','226370960418','226367091790','226309779476','226375176550','226309203625','226368335089','226393640653','226367224152','226382715800','226379128493','226300370865','226347943306','226365762044','226344270119','226385613983','226319840569','226329416015','226390152983','226366933276','226390913095','226365560066','226336708214'])->orderBy('created_at')->get();

            if ($res->count()>0) {
                return $res;
            }
            return null;
        }catch (\Exception $ex) {
            report($ex);
            return null;
        }
    }

    public function getPIForAutoSuccessWithoutCrossCheck()
    {
        try {
            $startDate = Carbon::now("Asia/Kolkata")->format("Y-m-d 00:00:00");
            $endDate = Carbon::now("Asia/Kolkata");
            $startDatedt=   Carbon::parse($startDate, "Asia/Kolkata")->subDays(10)->setTimezone("UTC")->format("Y-m-d H:i:s");
            $endDate = Carbon::parse($endDate, "Asia/Kolkata")->subHours(5)->setTimezone("UTC")->format("Y-m-d H:i:s");
            //  dd($startDatedt,$endDate);
            $res = $this->where('account_number','<>','13040200031632')->where('account_number','<>','13040200031608')->where('isget', 0)->where('amount','>=',500)->where('created_at','<',$endDate)->where('created_at','>',$startDatedt)->orderBy('created_at','desc')->get();
            //  $res = $this->whereIn('payment_utr',['226326777463','226315914489','226344989351','226356640155','226372513887','226349568833','226347130471','226328332494','226370924794','226313018054','226328073160','226312943675','226370960418','226367091790','226309779476','226375176550','226309203625','226368335089','226393640653','226367224152','226382715800','226379128493','226300370865','226347943306','226365762044','226344270119','226385613983','226319840569','226329416015','226390152983','226366933276','226390913095','226365560066','226336708214'])->orderBy('created_at')->get();

            if ($res->count()>0) {
                return $res;
            }
            return null;
        }catch (\Exception $ex) {
            report($ex);
            return null;
        }
    }
    public function getTransactionByBankUtr($payment_utr)
    {
        try {
            return  $this->where("payment_utr", $payment_utr)->where('isget',true)->first();
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

    public function getTransactionByDefBankUtr($payment_utr)
    {
        try {
            return  $this->where("payment_utr", $payment_utr)->first();
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
    public function MergeUpdateAmount($payment_utr,$amount)
    {
        try {
            $bank = $this->where("payment_utr", $payment_utr)
                ->where("isget", false)
                ->update([
                    "amount" => $amount
                ]);
            if($bank) {
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

    public function addBankTransaction(BankTransactionData $bankTransactionData){
        try {
            if(!$this->where('uniqe_hash',$bankTransactionData->uniqeHash)->exists() && !$this->where('payment_utr',$bankTransactionData->paymentUtr)->exists()){
                $this->uniqe_hash = $bankTransactionData->uniqeHash;
                $this->payment_utr = $bankTransactionData->paymentUtr;
                $this->amount = $bankTransactionData->amount;
                $this->payment_mode = "UPI";
                $this->upi_id = $bankTransactionData->upiId;
                $this->description = $bankTransactionData->description;
                $this->transaction_mode = "CR";
                $this->account_number = $bankTransactionData->accountNumber;
                $this->bank_name = $bankTransactionData->bankName;
                $this->udf1 = "";
                $this->udf2 = "";
                $this->udf3 = $bankTransactionData->udf3;
                $this->udf4 = $bankTransactionData->udf4;
                $this->udf5 = $bankTransactionData->udf5;
                $this->bank_txn_date = Carbon::now("Asia/Kolkata")->toDateString();
                $this->transaction_date = Carbon::now()->setTimezone('Asia/Kolkata')->toDateTimeString();
                $this->bank_created_at = Carbon::now();
                $this->entry_date = Carbon::now();
                $this->remark = "Added By Parse System";
                if($this->save()){
                    return true;
                }
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

    public function checkUtrAndAmount($utrNumber, $amount)
    {
        try {
            $res = $this->where('payment_utr', $utrNumber)->where('isget', 0)->first();
            if (isset($res)) {
                if($res->amount==$amount) {
                    if( \Illuminate\Support\Carbon::now()->diffInDays(\Illuminate\Support\Carbon::parse($res->created_at))<=10)
                    {
                        return $res;
                    }
                }
            }
            return null;
        }catch (\Exception $ex) {
            report($ex);
            return null;
        }
    }

    public function transactionMakeAsUsed($id){
        try {
            $res = $this->where('id', $id)->update(['isget' => 1]);
            if (isset($res)) {
                return $res;
            }
            return false;
        }catch (\Exception $ex) {
            report($ex);
            return false;
        }
    }

    public function getTransactionUpiId($bankRRN)
    {
        try {
            $upiId = $this->where('payment_utr', $bankRRN)->value('upi_id');
            if (isset($upiId)) {
                return $upiId;
            }
            return null;
        }catch (\Exception $ex) {
            report($ex);
            return null;
        }
    }

    public function getUsedTransactionList()
    {
        try {
            $result = $this->where('isget', 1)->where('is_temp', 0)->orderBy('created_at','DESC')->limit(10)->get(['payment_utr','upi_id','id']);
            if (isset($result) && !empty($result)) {
                return $result;
            }
            return null;
        }catch (\Exception $ex) {
            report($ex);
            return null;
        }
    }

    public function markAsGet($id)
    {
        try {
            $result = $this->where('id', $id)->update(['is_temp' => 1]);
            if ($result) {
                return true;
            }
            return false;
        }catch (\Exception $ex) {
            report($ex);
            return false;
        }
    }

    public function getSuccessUpiSum($upi)
    {
        try {
            $upiId = $this->where('upi_id', $upi)->select([
                 'upi_id as s_upi',
                DB::raw("COUNT(*) as total_success_upi"),
                DB::raw("SUM(amount) as sum_success_upi"),
            ]);
            if (isset($upiId)) {
                return $upiId->get();
            }
            return null;
        }catch (\Exception $ex) {
            report($ex);
            return null;
        }
    }
}
