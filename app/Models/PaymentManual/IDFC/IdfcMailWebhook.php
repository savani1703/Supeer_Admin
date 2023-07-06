<?php

namespace App\Models\PaymentManual\IDFC;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class IdfcMailWebhook extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_idfc_mail_webhook';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;


    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        "created_at_ist",
        "updated_at_ist"
    ];

    private $defaultAccountId = "ID000";
    private $defaultAccountIdPrefix = "ID";

    public function getCreatedAtIstAttribute()
    {
        $createdAtOriginal = $this->created_at;
        if (isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }

    public function getUpdatedAtIstAttribute()
    {
        $updatedAtOriginal = $this->updated_at;
        if (isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    public function getIdfcWebhook($filterData, $pageNo, $limit)
    {
        try {
            $data = $this->newQuery();
            if (isset($filterData)) {
                if (isset($filterData['account_number'])) {
                    $data->where("account_number", $filterData['account_number']);
                }
                if (isset($filterData['payout_id'])) {
                    $data->where("payout_id", $filterData['payout_id']);
                }
                if (isset($filterData['bank_rrn'])) {
                    $data->where("bank_rrn", $filterData['bank_rrn']);
                }
                if (isset($filterData['start_date']) && isset($filterData['end_date'])) {
                    $data->whereBetween("created_at", [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $data->select([
                'payout_id',
                'payout_amount',
                'account_number',
                'bank_rrn',
                'payment_from',
                'bank_date',
                'is_get',
                'is_data_sync',
                'error_message',
                'created_at',
                'updated_at',
            ]);

            $data->orderBy('created_at', 'desc');
            if ($data->count() > 0) {
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

