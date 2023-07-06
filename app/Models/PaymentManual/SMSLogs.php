<?php

namespace App\Models\PaymentManual;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class SMSLogs extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_sms_logs';
    protected $primaryKey = 'id';
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
    public function getSmsDateLongAttribute() {
        $originalData = $this->attributes['sms_date_long'];
        if(isset($originalData)) {
            $originalData = substr($originalData, 0, 10);
            return Carbon::createFromTimestamp($originalData, "Asia/Kolkata")->format('d-m-Y H:i:s');
        }
        return null;
    }

    public function getLogs($filterData, $pageNo, $limit) {
        try {
            $data = $this->newQuery();
            if(isset($filterData)) {
                if(isset($filterData['hardware_id'])) {
                    $data->where("hardware_id", $filterData['hardware_id']);
                }
                if(isset($filterData['start_date']) && isset($filterData['end_date'])) {
                    $data->whereBetween("created_at", [$filterData['start_date'], $filterData['end_date']]);
                }
                if(isset($filterData['is_get'])) {
                    $data->where("isget", $filterData['is_get']);
                }
            }
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $data->select([
                'id',
                'sms_logs',
                'hardware_id',
                'sms_date_long',
                'isget',
                'created_at'
            ]);

            $data->orderBy('created_at', 'desc');
            if($data->count() > 0){
                return $data->paginate($limit);
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
