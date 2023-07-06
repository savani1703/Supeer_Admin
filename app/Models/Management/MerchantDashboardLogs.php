<?php

namespace App\Models\Management;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class MerchantDashboardLogs extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_merchant_dashboard_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist"
    ];

    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }


    public function getDahboardLogs($merchantId, $filterData, $limit, $pageNo) {
        try {
            $dashboardLogs = $this->newQuery();
            $dashboardLogs->where("merchant_id", $merchantId);

            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['action_type']) && strcmp($filterData['action_type'], "ALL") !== 0) {
                    $dashboardLogs->where("action_type", $filterData['action_type']);
                }
                if(isset($filterData['request_ip'])) {
                    $dashboardLogs->where("request_ip", $filterData['request_ip']);
                }
            }

            $dashboardLogs->select([
                'action_type',
                'action',
                'request_ip',
                'user_agent',
                'created_at'
            ]);
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $dashboardLogs->orderBy('created_at', 'desc');
            if($dashboardLogs->count() > 0){
                return $dashboardLogs->paginate($limit);
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
