<?php

namespace App\Models\Management;

use App\Classes\Util\DigiPayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class PayoutManualReconciliation extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_payout_manual_recon';
    protected $primaryKey = 'payout_id';
    public $incrementing = false;
    public $timestamps = false;

    public function addReconPayout($payoutId, $merchantId, $manualPayBatchId)
    {
        try {
            if(!$this->where('payout_id',$payoutId)->exists()){
                $this->payout_id = $payoutId;
                $this->merchant_id = $merchantId;
                $this->manual_pay_batch_id = $manualPayBatchId;

                if($this->save()) {
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

    public function addReconPayoutForRecon($payoutId, $merchantId, $manualPayBatchId, $fileName, $id, $payoutAmount)
    {
        try {
            if(!$this->where('payout_id',$payoutId)->exists()){
                $this->payout_id = $payoutId;
                $this->payout_amount = $payoutAmount;
                $this->merchant_id = $merchantId;
                $this->manual_pay_batch_id = $manualPayBatchId;
                $this->file_name = $fileName;
                $this->bank_statement_id = $id;
                if($this->save()) {
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

    public function markAsUsed($array)
    {
        try {
            $result = $this->where('is_solved', false)->whereIn('payout_id', $array)->update(['is_solved' => true]);
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

    public function getUnMark()
    {
        try {
            $result = $this->where('is_solved', false)->get(['payout_id'])->toArray();
            if(count($result) > 0){
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

    public function getReconManualPayout($sheetValue)
    {
        try {
            $result = $this->where('is_solved', false)->limit($sheetValue)->get();
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

}
