<?php


namespace App\Models\Management;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class SupportLogs extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_support_logs';
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

    public function addLogsDetails($emailId, $action, $actionDetails)
    {
        try {
            $this->email_id         = $emailId;
            $this->action           = $action;
            $this->action_details   = $actionDetails;
            $this->client_ip        = request()->ip();
            $this->browser          = request()->userAgent();
            $this->save();

        }catch (QueryException $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }

    public function getSupportLogs($filterData,$limit, $page_no) {
        try {
            $supportLogs = $this->newQuery();
            if(isset($filterData) && sizeof($filterData) > 0) {

                if(isset($filterData['email_id']) && !empty($filterData['email_id'])) {
                    $supportLogs->where('email_id', $filterData['email_id']);
                }
                if(isset($filterData['action']) && !empty($filterData['action'])) {
                    $supportLogs->where('action', $filterData['action']);
                }

                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $supportLogs->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($page_no) {
                return $page_no;
            });

            $supportLogs->select([
                'id',
                'email_id',
                'action',
                'action_details',
                'client_ip',
                'created_at'
            ]);

            $supportLogs->orderBy('created_at', 'desc');
            if($supportLogs->count() > 0){
                return $supportLogs->paginate($limit);
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
