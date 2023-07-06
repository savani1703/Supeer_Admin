<?php

namespace App\Models\PaymentManual;

use App\Classes\Util\DigiPayUtil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AvailableBank extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_available_bank';
    protected $primaryKey = 'av_bank_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_seamless' => 'boolean',
        'is_active' => 'boolean'
    ];

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
        "last_bank_sync_ist",
        "last_bank_sync_mindeff_ist",
    ];

    private $defaultAccountId = "ab_00001";
    private $defaultAccountIdPrefix = "ab_";

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

    public function getLastBankSyncIstAttribute() {
        $updatedAtOriginal = $this->last_bank_sync;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }
    public function getLastBankSyncMindeffIstAttribute() {
        $updatedAtOriginal = $this->last_bank_sync;
        if(isset($updatedAtOriginal)) {
           return Carbon::now()->diffInMinutes(Carbon::parse($updatedAtOriginal, "UTC"));
        }
        return 20;
    }

    public function getMeta($filterData, $limit, $pageNo) {
        try {
            $meta = $this->newQuery();
            $meta->where("is_delete", "0");
            if(isset($filterData)) {
                if(isset($filterData['account_id']) && !empty($filterData['account_id'])) {
                    $meta->where("av_bank_id",$filterData['account_id'] );
                }
                if(isset($filterData['label']) && !empty($filterData['label'])) {
                    $meta->where("account_holder_name",$filterData['label'] );
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id'])) {
                    $meta->where("merchant_id",$filterData['merchant_id'] );
                }
                if(isset($filterData['upi_id']) && !empty($filterData['upi_id'])) {
                    $meta->where("upi_id",$filterData['upi_id'] );
                }
                if(isset($filterData['account_number']) && !empty($filterData['account_number'])) {
                    $meta->where("account_number",$filterData['account_number'] );
                }
            }
            $meta->select([
                "merchant_id",
                "av_bank_id as account_id",
                "account_holder_name as label",
                "vendor_id",
                "vendor_name",
                "account_number",
                "ifsc_code",
                "is_account_flow_active",
                "upi_id",
                "bank_name",
                "is_active",
                "is_auto_login",
                "live_bank_balance",
                "is_seamless",
                "min_limit",
                "max_limit",
                "turn_over",
                "current_turn_over",
                "available_method",
                "created_at",
                "updated_at"
            ]);

            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });

            $meta->orderBy('created_at', 'desc');

            if($meta->count() > 0){
                return $meta->paginate($limit);
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

    public function getAccountId() {
        try {
            $lastAccountId = $this->orderBy("created_at", "desc")->value("av_bank_id");
            return DigiPayUtil::generateAvailableBankId($lastAccountId ?? null);
        } catch (QueryException $ex) {
            throw $ex;
        }
    }

    public function addMeta($formData, $accountId) {
        try {
            $this->av_bank_id = $accountId;
            $this->merchant_id = $accountId;
            $this->account_holder_name = $formData['account_holder_name'];
            $this->account_number = $formData['account_number'];
            $this->ifsc_code = $formData['ifsc_code'];
            $this->upi_id = $formData['upi_id'];
            $this->bank_name = $formData['bank_name'];
            $this->vendor_id = "-";
            $this->vendor_name = "-";
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

    public function updateMetaStatus($metaId, $status) {
        try {
            if($this->where("av_bank_id", $metaId)->update(["is_active" => $status])) {
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

    public function updateMetaAutoLoginStatus($metaId, $status) {
        try {
            if($this->where("av_bank_id", $metaId)->update(["is_auto_login" => $status])) {
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

    public function updateMetaMinLimit($metaId, $minLimit) {
        try {
            if($this->where("av_bank_id", $metaId)->update(["min_limit" => $minLimit])) {
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

    public function updateMetaMaxLimit($metaId, $maxLimit) {
        try {
            if($this->where("av_bank_id", $metaId)->update(["max_limit" => $maxLimit])) {
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

    public function updateMetaTurnOver($metaId, $turnOver) {
        try {
            if($this->where("av_bank_id", $metaId)->update(["turn_over" => $turnOver])) {
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

    public function updateMetaMethod($metaId, $method) {
        try {
            if($this->where("av_bank_id", $metaId)->update(["available_method" => $method])) {
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

    public function getPgLabel($metaId) {
        try {
            $data = $this->where("av_bank_id", $metaId)->value("account_holder_name");
            if(isset($data)) {
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

    public function getAllActivePgMeta() {
        try {
            $data = $this->where("is_active", "1")
                ->select([
                    "av_bank_id as account_id",
                    "account_holder_name as label",
                    "merchant_id",
                    "is_seamless",
                    "available_method",
                    "account_number",
                    "bank_name",
                    "upi_id",
                ])
                ->get();
            if($data->count() > 0) {
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

    public function getAllPgMeta() {
        try {
            $data = $this->select([
                "av_bank_id as account_id",
                "account_holder_name as label",
                "merchant_id",
                "is_seamless",
                "available_method",
                "account_number",
                "bank_name",
                "upi_id",
            ])
                ->orderBy("created_at", "desc")
                ->get();
            if($data->count() > 0) {
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

    public function getMetaForTransactionById($metaId) {
        try {
            $data = $this->where("av_bank_id", $metaId)
                ->select([
                    "av_bank_id as account_id",
                    "account_holder_name as label",
                    "upi_id",
                    "account_number",
                    "ifsc_code",
                    "bank_name",
                    "is_account_flow_active",
                ])
                ->first();
            if(isset($data)) {
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

    public function getPayInMeta($metaId) {
        try {
            $data = $this->where("av_bank_id", $metaId)->first();
            if(isset($data)) {
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

    public function getMetaByMerchantId($merchantId)
    {
        try {
            $data = $this->where('merchant_id', $merchantId)->first();
            if(isset($data)) {
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }


    public function validateFormData($formData, $accountId) {
        $validator = Validator::make($formData, [
            "account_holder_name"   => "required",
            "account_number"        => "required",
            "ifsc_code"             => "required",
            "upi_id"                => "required",
            "bank_name"             => "required"
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        if($this->where("av_bank_id", $accountId)->where("account_number", $formData['account_number'])->where("upi_id",  $formData['upi_id'])->exists()) {
            throw new \Exception("Meta Already Exists");
        }
    }


    public function getRenderConfig() {
        return [
            "show_columns" => [
                "id",
                "account_id",
                "label",
                "merchant_id",
                "vendor",
                "account",
                "is_active",
                "min_limit",
                "max_limit",
                "turn_over",
                "current_turn_over",
                "available_method",
            ],
            "editable_columns" => [
                "is_active",
                "is_auto_login",
                "min_limit",
                "max_limit",
                "turn_over",
                "available_method",
            ],
            "add_meta_columns" => [
                "account_holder_name",
                "account_number",
                "ifsc_code",
                "upi_id",
                "bank_name"
            ]
        ];
    }

    public function getTodaySyncBank($syncDate)
    {
        try {
            $data = $this->newQuery();
            $data->where(DB::raw("DATE(last_bank_sync)"), $syncDate);
            $data->where('is_active',true);
            $data->orderBy("account_holder_name");
            $result = $data->get();
            if($result->count() > 0) {
                $result=json_decode(json_encode($result),false);
                return $result;
            }
            return null;
        } catch (QueryException $ex) {
            report($ex);
            return null;
        }
    }

    public function checkDetailsIsValidate($pgId, $bankName)
    {
        try {
            $result = $this->where('av_bank_id', $pgId)->where('bank_name', $bankName)->exists();
            if($result){
                return true;
            }
            return false;
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function getBankDetailsByNum($accountNumber)
    {
        try {
            $result = $this->where('account_number',$accountNumber)->first();
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function avBankId($bankid)
    {
        try {
            $result = $this->where('av_bank_id',$bankid)->first();
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getPayInMetaForBankSync($metaId) {
        try {
            $data = $this->where("av_bank_id", $metaId)->first();
            if(isset($data)) {
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

    public function getAccountDetailsByAccountNumber($accountNumber) {
        try {
            $data = $this->where("account_number", $accountNumber)->first();
            if(isset($data)) {
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

    public function getAvailableBank($bankName)
    {
        try {
            $data=$this->newQuery();
            if (isset($bankName) && !empty($bankName)){
                $data->where('bank_name',$bankName);
            }
            $data->select([
                "account_number",
                "account_holder_name",
                ])
                ->orderBy('account_holder_name', 'asc');

            if($data->count() > 0) {
                return $data->get();
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
