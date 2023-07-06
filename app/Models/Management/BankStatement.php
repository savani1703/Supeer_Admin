<?php

namespace App\Models\Management;

use App\Models\PaymentManual\AvailableBank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class BankStatement extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_bank_statement_file';
    protected $primaryKey = 'account_number';
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

    public function getBankName()
    {
        try {
            $result=$this->select([
                'bank_name',
            ])->get();
            if ($result) {
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

    public function getStatement($filterData, $limit, $pageNo)
    {
        try {
            $statement = $this->newQuery();


            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['is_get'])) {
                    $statement->where("is_get", $filterData['is_get']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $statement->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $statement->with('accountDetail')->orderBy('created_at', 'desc');

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
    public function accountDetail(){
        return $this->belongsTo(AvailableBank::class,'account_number','account_number')->select(['account_holder_name','account_number','ifsc_code','upi_id']);
    }
}
