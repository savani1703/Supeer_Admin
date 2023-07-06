<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class CustomerLevel extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_customer_level';
    protected $primaryKey = 'customer_id';
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

    public function getCustomers($filterData, $limit, $pageNo)
    {
        try {
            $customers = $this->newQuery();

            if(isset($filterData)) {

                if(isset($filterData['merchant_id'])) {
                    $customers->where('merchant_id', $filterData['merchant_id']);
                }

                if(isset($filterData['customer_id'])) {
                    $customers->where('customer_id', $filterData['customer_id']);
                }

                if(isset($filterData['pg_method'])) {
                    $customers->where('pg_method', $filterData['pg_method']);
                }
                if(isset($filterData['customerFilter'])) {
                    if(strcmp($filterData['customerFilter'],'hasMostUpi') === 0){
                        $customers->where('pg_method','FASTUPI')->orderBy('total_success_upi_id','DESC');
                    }
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $customers->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $customers->with(["merchantDetails"]);
            $customers->orderBy('created_at', 'desc');
            if($customers->count() > 0){
                return $customers->paginate($limit);
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

    public function merchantDetails()
    {
        return $this->belongsTo(MerchantDetails::class, "merchant_id", "merchant_id");
    }

    public function updateCustomerBlockStatus($customerId, $merchantId, $pgMethod, $status)
    {
        try {
            return $this->where("customer_id", $customerId)->where("merchant_id", $merchantId)->where("pg_method", $pgMethod)->update(["is_block" => $status]);
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

    public function getCustomerLeveling($merchantId, $customerId)
    {
        try {
            $result = $this->where('merchant_id', $merchantId)->where('customer_id', $customerId)->where('pg_method','FASTUPI')->first(['user_security_level','created_at']);
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getCustlevel($filterData)
    {
        try {
            $result = $this->where('is_block','1')->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']])->get();
            if($result->count() >0 ){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getCustomerLevelingDetailsForUpiMap()
    {
        try {
            $result = $this->where('pg_method','FASTUPI')->orderBy('created_at','DESC')->get();
            if($result->count()){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
    public function updateTotalSuccessUpiId($customerId, $totalUpi)
    {
        try {
            $result = $this->where('customer_id',$customerId)->update(['total_success_upi_id' => $totalUpi]);
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function increaseTotalSuccessUpiId($customerId, $merchantId)
    {
        try {
            $result = $this->where('customer_id',$customerId)->where('merchant_id',$merchantId)->increment('total_success_upi_id');
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function getCustomerDetailsForLevel($filterType, $filterData)
    {
        try {

            $customers = $this->newQuery();
            if(isset($filterType) && !empty($filterType)){
                if(strcmp($filterType,'L1') === 0){
                    $customers->where('user_security_level',1);
                }else if(strcmp($filterType,'L2') === 0){
                    $customers->where('user_security_level',2);
                }else if(strcmp($filterType,'L5') === 0){
                    $customers->where('user_security_level',5);
                }
            }

            if(isset($filterData)) {
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $customers->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            $customers->orderBy('created_at', 'desc');

            if($customers->count()){
                return $customers->get();
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getcustByDate($customerId,$filterData)
    {
        try {
            $data=$this->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']])->select('customer_id','user_security_level')->where('customer_id',$customerId)->first();
            if($data){
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
}

