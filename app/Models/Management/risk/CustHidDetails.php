<?php

namespace App\Models\Management\risk;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class CustHidDetails extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_customer_hid_details';
    protected $primaryKey = 'device_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    protected $casts = [
        'is_keep' => 'boolean',
        'is_get' => 'boolean'
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

    public function GetCustHidDetail($filterData,$limit, $pageNo){
        try {
            $custDetail=$this->newQuery();
            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['device_id']) && !empty($filterData['device_id'])) {
                    $custDetail->where('device_id', $filterData['device_id']);
                }
                if(isset($filterData['customerFilter'])) {
                    if(strcmp($filterData['customerFilter'],'hasMostCustomerId') === 0){
                        $custDetail->orderBy('total_customer_id','DESC');
                    }
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $custDetail->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            $custDetail->select(['device_id','total_customer_id','created_at']);

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $custDetail->orderBy('created_at', 'desc');
            if($custDetail->count() > 0){
                return $custDetail->paginate($limit);
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

    public function getCustomerHidData()
    {
        try {
            $result = $this->where('is_get',false)->orderBy('created_at', 'desc')->limit(1)->get();
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

    public function setTotalCustomerId($deviceId, $totalCustomerId)
    {
        try {
            $result = $this->where('device_id',$deviceId)->update(['total_customer_id' => $totalCustomerId]);
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

    public function setMarkAsUsed($deviceId)
    {
        try {
            $result = $this->where('device_id',$deviceId)->update(['is_get' => true]);
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

    public function resetAllGetData()
    {
        try {
            $result = $this->where('is_get',true)->update(['is_get' => false]);
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
}
