<?php

namespace App\Models\Bouncer;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;


class BouncerData extends Model
{
    protected $connection = 'bouncer';
    protected $table = 'tbl_bouncer_data';
    protected $primaryKey = 'token';
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

    public function getBouncerData($filterData, $pageNo, $limit) {
        try {
            $bouncerData = $this->newQuery();
            if(isset($filterData) && sizeof($filterData) > 0) {

                if(isset($filterData['token']) && !empty($filterData['token'])) {
                    $bouncerData->where('token', $filterData['token']);
                }
                if(isset($filterData['transaction_id']) && !empty($filterData['transaction_id'])) {
                    $bouncerData->where('transaction_id', $filterData['transaction_id']);
                }
                if(isset($filterData['pg_name']) && !empty($filterData['pg_name']) && strcmp(strtolower($filterData['pg_name']), "all") !== 0) {
                    $bouncerData->where('pg_name', $filterData['pg_name']);
                }
                if(isset($filterData['is_call']) && !empty($filterData['is_call']) && strcmp(strtolower($filterData['is_call']), "all") !== 0) {
                    $filterData['is_call'] = strcmp($filterData['is_call'], "yes") === 0;
                    $bouncerData->where('is_call', $filterData['is_call']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $bouncerData->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $bouncerData->orderBy('created_at', 'desc');
            if($bouncerData->count() > 0){
                return $bouncerData->paginate($limit);
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
            return false;
        }
    }
}
