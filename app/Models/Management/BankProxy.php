<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class BankProxy extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_bank_proxy_list';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function getBankProxyList($acc_number=null)
    {
        try {
            $q = $this->newQuery();
            if(isset($acc_number)) {
                $q->where('label_name',$acc_number);
                $q->where('is_active',1);
            }
            $data=   $q->orderBy("id", "asc")->get();
            if($data->count() > 0) {
                return $data;
            }
            return null;
        } catch (QueryException $ex) {
            report($ex);
            return null;
        }
    }

    public function checkIsExists($label, $proxyIp)
    {
        try {
            return $this->where("label_name", $label)->where("ip_proxy", $proxyIp)->exists();
        } catch (QueryException $ex) {
            report($ex);
            return true;
        }
    }

    public function addBankProxy($label, $proxyIp)
    {
        try {
            $this->label_name = $label;
            $this->ip_proxy = $proxyIp;
            if($this->save()) {
                return true;
            }
            return false;
        } catch (QueryException $ex) {
            report($ex);
            return false;
        }
    }
    public function deleteBankProxy($id) {
        try {
            if($this->where("id", $id)->delete()) {
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

    public function editBankProxyStatus($id,$status) {
        try {
            if($this->where("id", $id)->update(["is_active" => $status])) {
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
}
