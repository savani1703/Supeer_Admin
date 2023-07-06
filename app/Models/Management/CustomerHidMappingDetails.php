<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class CustomerHidMappingDetails extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_customer_hid_mapping_details';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_get' => 'boolean'
    ];

    public function getPendingMapDetails()
    {
        try {
            $result = $this->where('is_get', false)->orderBy('created_at','DESC')->limit(1)->get();
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

    public function markAsUsed($id)
    {
        try {
            $result = $this->where('id',$id)->update(['is_get' => true]);
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
