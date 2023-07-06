<?php

namespace App\Models\PaymentManual;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerSuccessUpiMapping extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_customer_success_upi_mapping';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function addCustomerSuccessUpiMap($merchantId, $customerId, $successUpiId, $upiId)
    {
        try {
            if (!$this->where('merchant_id', $merchantId)->where('customer_id', $customerId)->where('upi_id', $upiId)->where('success_upi_id', $successUpiId)->exists()) {
                $this->merchant_id = $merchantId;
                $this->customer_id = $customerId;
                $this->upi_id = $upiId;
                $this->success_upi_id = $successUpiId;
                if ($this->save()) {
                    return true;
                }
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

    public function getTotalUpi($customerId)
    {
        try {
            $result = $this->where('customer_id', $customerId)->count();
            if ($result) {
                return $result;
            }
            return 0;
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return 0;
        }
    }

    public function getMapDetailsById($customerId)
    {
        try {
            $result = $this->newQuery();
            $result->where('customer_id', $customerId);
            if($result->count() > 0){
                return $result->get();
            }
            return null;
        } catch (QueryException $ex) {
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
