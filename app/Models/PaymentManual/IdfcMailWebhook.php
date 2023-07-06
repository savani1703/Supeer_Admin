<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class IdfcMailWebhook extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_idfc_mail_webhook';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_get' => 'boolean'
    ];

    public function setMailWebhook($data){
        try {
            $this->mail_data = $data;
            if($this->save()){
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

    public function getPendingWebhook()
    {
        try {
            $result = $this->where('is_get', false)->first();
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

    public function markAsUsed($id)
    {
        try {
            $result = $this->where('id', $id)->update(['is_get' => true]);
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

    public function updateRecData($id, $payoutId, $payoutAmount, $referenceNumber, $paymentFrom, $bankDate, $accountNumber)
    {
        try {
            $result = $this->where('id', $id)->update(['payout_id' => $payoutId, 'payout_amount' => $payoutAmount, 'bank_rrn' => $referenceNumber, 'payment_from' => $paymentFrom, 'bank_date' => $bankDate, 'account_number' => $accountNumber]);
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

    public function setRemark($id, $errorMessage)
    {
        try {
            $this->where('id', $id)->update(['is_get' => true, 'error_message' => $errorMessage]);
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

    public function getPayoutIdByUtrAndAcc($utrNumber, $accountNumber){
        try {
            $result = $this->where('account_number', $accountNumber)->where('bank_rrn', $utrNumber)->value('payout_id');
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

    public function checkUtrExists($utrNumber, $accountNumber)
    {
        try {
            $result = $this->where('account_number', $accountNumber)->where('bank_rrn', $utrNumber)->exists();
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

    public function getBankRRN($payoutId, $accountNumber)
    {
        try {
            $result = $this->where('payout_id', $payoutId)->where('account_number', $accountNumber)->orderBy('created_at', 'desc')->value('bank_rrn');
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

}
