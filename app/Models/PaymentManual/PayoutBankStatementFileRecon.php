<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PayoutBankStatementFileRecon extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_payout_bank_statement_file_recon';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function addUtr($utrNumber, $accountNumber, $fileName, $bankName, $amount, $date){
        try {
            if(!$this->where('bank_rrn', $utrNumber)->exists()){
                $this->bank_rrn = $utrNumber;
                $this->amount = $amount;
                $this->account_number = $accountNumber;
                $this->file_name = $fileName;
                $this->bank_name = $bankName;
                $this->bank_date = $date;
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

    public function deleteUtr($utrNumber)
    {
        try {
            if($this->where('bank_rrn', $utrNumber)->delete()){
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
