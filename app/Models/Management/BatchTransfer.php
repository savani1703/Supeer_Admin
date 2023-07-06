<?php

namespace App\Models\Management;

use App\Classes\Util\DigiPayUtil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BatchTransfer extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_batch_transfer';
    protected $primaryKey = "batch_id";
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    protected $casts = [
        'mark_as_used' => 'boolean'
    ];

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

    public function addManualPayoutBatch($batchId, $metaId, $bankName, $debitAccount, $payoutAmount, $payoutRecord)
    {
        try {
            $this->batch_id = $batchId;
            $this->pg_id = $metaId;
            $this->bank_name = $bankName;
            $this->debit_account = $debitAccount;
            $this->payout_record = $payoutRecord;
            $this->payout_amount = $payoutAmount;
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

    public function checkBatchIsExist($batchId)
    {
        try {
            return $this->where("batch_id", $batchId)->exists();
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

    public function getBatchTransferList($filter_data, $limit, $page_no)
    {
        try {
            if (isset($filter_data)) {
                if (isset($filter_data['start_date']) && !empty($filter_data['start_date'])) {
                    $filter_data['start_date'] = DigiPayUtil::TO_UTC($filter_data['start_date']);
                }
                if (isset($filter_data['end_date']) && !empty($filter_data['end_date'])) {
                    $filter_data['end_date'] = DigiPayUtil::TO_UTC($filter_data['end_date']);
                }
            }

            $data = $this->newQuery();
            if (isset($filter_data)) {
                if (isset($filter_data['batch_id']) && !empty(isset($filter_data['batch_id']))) {
                    $data->where("batch_id", $filter_data['batch_id']);
                }
                if (isset($filter_data['bank_name']) && !empty(isset($filter_data['bank_name']))) {
                    $data->where("bank_name", $filter_data['bank_name']);
                }
                if (isset($filter_data['debit_account']) && !empty(isset($filter_data['debit_account']))) {
                    $data->where("debit_account", $filter_data['debit_account']);
                }
                if (isset($filter_data['start_date']) && !empty($filter_data['start_date']) && isset($filter_data['end_date']) && !empty($filter_data['end_date'])) {
                    $data->whereBetween('created_at', [$filter_data['start_date'], $filter_data['end_date']]);
                }
            }
            Paginator::currentPageResolver(function () use ($page_no) {
                return $page_no;
            });

            $data->select([
                'batch_id',
                'bank_name',
                'debit_account',
                'payout_record',
                'payout_amount',
                'file_data',
                'file_name',
                'mark_as_used',
                'pg_id',
                'created_at'
            ]);


            $data->orderBy('created_at', 'DESC');
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
            return false;
        }
    }

    public function getBatchById($batch_id)
    {
        try {
            $data = $this->where("batch_id", $batch_id)->first();
            if (isset($data)) {
                return $data;
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

    public function updateBatchFileData($batch_id, $generatedFileData, $fileName)
    {
        try {
            if ($this->where("batch_id", $batch_id)->update(["file_data" => $generatedFileData, "file_name" => $fileName])) {
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

    public function markAsUsed($batch_id)
    {
        try {
            if ($this->where("batch_id", $batch_id)->update(["mark_as_used" => true])) {
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

    public function addManualPayoutBatchCustom($batchId, $metaId, $bankName, $debitAccount, $payoutAmount, $payoutRecord, $generatedFileData, $fileName)
    {
        try {
            $this->batch_id = $batchId;
            $this->pg_id = $metaId;
            $this->bank_name = $bankName;
            $this->debit_account = $debitAccount;
            $this->payout_record = $payoutRecord;
            $this->payout_amount = $payoutAmount;
            $this->file_data = $generatedFileData;
            $this->file_name = $fileName;
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

    public function addManualPayoutBatchCustomForIDFC($batchId, $metaId, $bankName, $debitAccount, $payoutAmount, $payoutRecord)
    {
        try {
            $this->batch_id = $batchId;
            $this->pg_id = $metaId;
            $this->bank_name = $bankName;
            $this->debit_account = $debitAccount;
            $this->payout_record = $payoutRecord;
            $this->payout_amount = $payoutAmount;
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

