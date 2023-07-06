<?php


namespace App\Models\Management;


use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class TransactionEvent extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_transaction_event';
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

    public function getTransactionsEvent($filterData, $limit, $pageNo)
    {
        try {
            $txnEvent = $this->newQuery();
            if(isset($filterData) && sizeof($filterData) > 0) {

                if(isset($filterData['event_id']) && !empty($filterData['event_id'])) {
                    $txnEvent->where('event_id', $filterData['event_id']);
                }
                if(isset($filterData['event_type']) && !empty($filterData['event_type'])) {
                    $txnEvent->where('event_type', $filterData['event_type']);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $txnEvent->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $txnEvent->select([
                'id',
                'event_id',
                'event_type',
                'webhook_status_code',
                'webhook_response',
                'sent_webhook_data',
                'created_at',
                'updated_at'
            ]);

            $txnEvent->orderBy('created_at', 'desc');
            if($txnEvent->count() > 0){
                return $txnEvent->paginate($limit);
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

    public function markAsResend($eventId)
    {
        try {
            $this->where("event_id", $eventId)->update([
                "webhook_status_code" => 201
            ]);
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }

    public function checkWebhookSent($transaction)
    {
        try {
            if($this->where('event_id',$transaction)->where('webhook_status_code','200')->exists()){
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
