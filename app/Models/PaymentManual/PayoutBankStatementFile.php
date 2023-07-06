<?php

namespace App\Models\PaymentManual;

use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

/**
 * @mixin Builder
 */
class PayoutBankStatementFile extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_payout_bank_statement_file';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_get' => 'boolean',
        'is_running' => 'boolean'
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

   public function getPayoutStatement($filterData, $limit, $pageNo)
    {
        try {
            $statement = $this->newQuery();

            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $statement->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            $statement->with(['acountDetail']);
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $statement->orderBy('created_at','desc');
            if($statement->count() > 0){

                return $statement->paginate($limit);
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

    public function acountDetail(){
       return $this->belongsTo(IDFCPayoutMeta::class,"account_number","debit_account");
    }

    public function uploadStatementFile($fileName)
    {
        try {
            $this->file_name = $fileName;
            $this->is_get = 0;
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

    public function checkBankFileExist($fileName)
    {
        try {
            $result = $this->where('file_name', $fileName)->exists();
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

    public function checkFileIsRunning()
    {
        try {
            $result = $this->where('is_running', true)->exists();
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            report($ex);
            return true;
        }
    }

    public function getUnParseBankFiles(){
        try {
            $result = $this->where('is_get', false)->first();
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function markAsRunning($id)
    {
        try {
            $this->where('id', $id)->update(['is_running' => true]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function markAsUsed($id)
    {
        try {
            $this->where('id', $id)->update(['is_get' => true]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function markAsError($id)
    {
        try {
            $this->where('id', $id)->update(['is_get' => true, 'is_running' => false]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function setRemark($id, $remark)
    {
        try {
            $this->where('id', $id)->update(['remark' => $remark]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function setAccountNumber($id, $accountNumber)
    {
        try {
            $this->where('id', $id)->update(['account_number' => $accountNumber]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function addTotalCount($id, $totalTransaction)
    {
        try {
            $this->where('id', $id)->update(['total_count' => $totalTransaction]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function addProgressCount($id, $progress)
    {
        try {
            $this->where('id', $id)->update(['progress' => $progress]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function totalAddedUtr($id, $addedUtr)
    {
        try {
            $this->where('id', $id)->update(['total_added_utr' => $addedUtr,'is_running' => false]);
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }
}
