<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class BankParseUtr extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_bank_parse_utr';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function addBankParseUtr($bankStatementId, $fileName, $bankUtr, $amount){
        try {
            if(!$this->where('bank_statement_id', $bankStatementId)->where('file_name', $fileName)->where('bank_utr', $bankUtr)->exists()){
                $this->bank_statement_id = $bankStatementId;
                $this->file_name = $fileName;
                $this->bank_utr = $bankUtr;
                $this->amount = $amount;
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

}
