<?php

namespace App\Models\PaymentManual;

use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class MailReader extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_mail_reader';
    protected $primaryKey = 'av_bank_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    protected $casts = [
        "is_active" => "boolean"
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

    public function getMailReader($filterData, $pageNo, $limit) {
        try {
            $data = $this->newQuery();
            $data->with("bankDetails");
            if(isset($filterData)) {
                if(isset($filterData['av_bank_id'])) {
                    $data->where("av_bank_id", $filterData['av_bank_id']);
                }
                if(isset($filterData['username'])) {
                    $data->where("username", $filterData['username']);
                }
                if(isset($filterData['mail_sender'])) {
                    $data->where("mail_sender", $filterData['mail_sender']);
                }
                if(isset($filterData['mail_from'])) {
                    $data->where("mail_from", $filterData['mail_from']);
                }
                if(isset($filterData['provider'])) {
                    $data->where("provider", $filterData['provider']);
                }
                if(isset($filterData['start_date']) && isset($filterData['end_date'])) {
                    $data->whereBetween("created_at", [$filterData['start_date'], $filterData['end_date']]);
                }
            }
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $data->select([
                'av_bank_id',
                'username',
                'mail_sender',
                'mail_from',
                'provider',
                'is_active',
                'updated_at',
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

    public function updateReaderStatus($bankId, $status) {
        try {
            if($this->where("av_bank_id", $bankId)->update(["is_active" => $status])) {
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

    public function addMailReader($bankId, $username, $password, $mailSender, $mailFrom, $provider) {
        try {
            $this->av_bank_id = $bankId;
            $this->username = $username;
            $this->password = $password;
            $this->mail_sender = $mailSender;
            $this->mail_from = $mailFrom;
            $this->provider = $provider;
            if($this->save()) {
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

    public function getAllMailDetailsForIDfc(){
        try {
            $result = $this->where('is_active', true)->with('idfcDetails')->get();
            if($result->count()){
                return $result;
            }
            return null;
        }catch (\Exception $ex){
            return null;
        }
    }

    public function getMailDetailsForIDfcById($avBankId)
    {
        try {
            $result = $this->where('av_bank_id', $avBankId)->with('idfcDetails')->first();
            if($result){
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


    public function idfcDetails() {
        return $this->belongsTo(IDFCPayoutMeta::class, "av_bank_id", "account_id");
    }

    public function bankDetails() {
        return $this->belongsTo(AvailableBank::class, "av_bank_id", "av_bank_id");
    }

}
