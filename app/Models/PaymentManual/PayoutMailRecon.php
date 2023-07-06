<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PayoutMailRecon extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_payout_mail_recon';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function addPayoutMailRecon($payoutId, $amount, $accountNumber){
        try {
            if(!$this->where('payout_id', $payoutId)->where('account_number', $accountNumber)->exists()){
                $this->payout_id        = $payoutId;
                $this->amount           = $amount;
                $this->account_number   = $accountNumber;
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

    public function checkPayoutIdExists($payoutId){
        try {
            if($this->where('payout_id', $payoutId)->exists()){
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
