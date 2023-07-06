<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class BankStatementFile extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_bank_statement_file';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'isget' => 'boolean',
        'is_running' => 'boolean'
    ];

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

    public function setRemark($id, $remark)
    {
        try {
            $this->where('id', $id)->update(['remark' => $remark]);
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

    public function markAsRunning($id)
    {
        try {
            $this->where('id', $id)->update(['is_running' => true]);
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

    public function setAccountNumber($id, $accountNumber)
    {
        try {
            $this->where('id', $id)->update(['account_number' => $accountNumber]);
        }catch (QueryException $ex){
            report($ex);
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
}
