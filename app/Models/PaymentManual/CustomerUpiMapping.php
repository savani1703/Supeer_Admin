<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
/**
 * @mixin Builder
 */
class CustomerUpiMapping extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_customer_upi_mapping';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;
    protected $casts = [
        'is_last' => 'boolean'
    ];
    public function addCustomerUpiMappingDetails($merchantId, $customerId, $upiId){
        try {
            if(!$this->where('merchant_id', $merchantId)->where('customer_id', $customerId)->where('upi_id', $upiId)->exists()){
                $this->merchant_id = $merchantId;
                $this->customer_id = $customerId;
                $this->upi_id = $upiId;
                $this->is_last = true;
                if($this->save()){
                    return true;
                }
            }
            return false;
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function setCustomerLastUsedUpi($merchantId, $customerId, $upiId)
    {
        try {
            $result = $this->where('merchant_id',$merchantId)->where('customer_id', $customerId)->where('upi_id','!=', $upiId)->update([
                'is_last'=> false
            ]);
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getLastUpiIdByCustomerId($merchantId, $customerId)
    {
        try {
            $result = $this->where('merchant_id',$merchantId)->where('customer_id',$customerId)->where('is_last',true)->value('upi_id');
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
    public function getCustomerIDFromUPI($merchantId,$upiId)
    {
        try {
            $result = $this->where('merchant_id',$merchantId)->where('upi_id',$upiId)->get();
            if($result->count()>0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
    public function getCustomerIDFromUPILike($merchantId,$upiId)
    {
        try {
            $result = $this->where('merchant_id',$merchantId)->where('upi_id','like',$upiId.'%')->get();
            if($result->count()>0){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
}
