<?php

namespace App\Models\PaymentManual;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class LateSuccess extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_late_success';
    protected $primaryKey = 'transaction_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return \Carbon\Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
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

    public function addRecord($transaction_id,$payment_utr)
    {
        if(!$this->where('transaction_id',$transaction_id)->where('utr_number',$payment_utr)->exists()) {
            try {
                $this->transaction_id = $transaction_id;
                $this->utr_number = $payment_utr;
                if ($this->save()) {
                    return true;
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
    }
    public function getRecords()
    {
        try {
            $startDate = Carbon::now("Asia/Kolkata")->format("Y-m-d 00:00:00");
            $startDatedt=   Carbon::parse($startDate, "Asia/Kolkata")->setTimezone("UTC");
           return $this->where('created_at','>',$startDatedt)->get();
        } catch (QueryException $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);

        }
        return false;
    }
    public function getLateSuccessData($filterData, $pageNo, $limit) {
        try {

            $data = $this->newQuery();

            if(isset($filterData)) {
                if(isset($filterData['search_value']) && !empty($filterData['search_value'])  && strlen($filterData['search_value']) > 3) {
                    $data->where('transaction_id', 'like', "%" . $filterData['search_value']);
                    $data->orWhere('utr_number', 'like', "%" . $filterData['search_value']);
                }
                if(isset($filterData['start_date']) && isset($filterData['end_date'])) {
                    $data->whereBetween("created_at", [$filterData['start_date'], $filterData['end_date']]);
                }
            }

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

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
