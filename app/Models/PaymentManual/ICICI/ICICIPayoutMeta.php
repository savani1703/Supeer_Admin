<?php

namespace App\Models\PaymentManual\ICICI;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\PgName;
use App\Models\Management\ProxyList;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ICICIPayoutMeta extends Model
{
    protected $connection = 'payment_manual';
    protected $table = 'tbl_icici_payout_meta';
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

    private $defaultAccountId = "IC000";
    private $defaultAccountIdPrefix = "IC";

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

    public function getLastCheckBalanceAtIstAttribute() {
        $updatedAtOriginal = $this->last_check_balance_at;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    public function getMeta($filterData, $limit, $pageNo) {
        try {
            $meta = $this->newQuery();

            if(isset($filterData)) {
                if(isset($filterData['account_id']) && !empty($filterData['account_id'])) {
                    $meta->where("account_id", $filterData['account_id']);
                }
                if(isset($filterData['label']) && !empty($filterData['label'])) {
                    $meta->where("label", $filterData['label'] );
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id'])) {
                    $meta->where("merchant_id", $filterData['merchant_id'] );
                }
            }
            $meta->select([
                "id",
                "account_id",
                "label",
                "email_id",
                "merchant_id",
                "debit_account",
                "bank_code",
                "is_active",
                "min_limit",
                "max_limit",
                "max_count_limit",
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
            $lastAccountId = $this->orderBy("created_at", "desc")->value("account_id");
            if(!isset($lastAccountId)) {
                $lastAccountId = $this->defaultAccountId;
            }
            return DigiPayUtil::generatePayoutMetaId($lastAccountId, $this->defaultAccountIdPrefix);
        } catch (QueryException $ex) {
            throw $ex;
        }
    }

    public function addMeta($formData, $accountId) {
        try {
            $this->account_id = $accountId;
            $this->label = $formData['label'];
            $this->email_id = $formData['email_id'];
            $this->merchant_id = PgName::ICICI.$accountId;
            $this->debit_account = $formData['debit_account'];
            $this->bank_code = $formData['bank_code'];
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
            if($this->where("account_id", $metaId)->update(["is_active" => $status])) {
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
            if($this->where("account_id", $metaId)->update(["min_limit" => $minLimit])) {
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
            if($this->where("account_id", $metaId)->update(["max_limit" => $maxLimit])) {
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
    public function updateMetaMaxCountLimit($metaId, $maxCountLimit) {
        try {
            if($this->where("account_id", $metaId)->update(["max_count_limit" => $maxCountLimit])) {
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

    public function getPayoutMetaById($pgId)
    {
        try {
            $data = $this->where('account_id', $pgId)->where('is_active', true)->first();
            if(isset($data)){
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getAllActivePgMeta()
    {
        try {
            $data = $this->where('is_active', true)->get();
            if($data->count() > 0){
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getPayoutMetaByIdForStatusCheck($pgId)
    {
        try {
            $data = $this->where('account_id', $pgId)->first();
            if(isset($data)){
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getMetaForPayoutByMetaId($pgId)
    {
        try {
            $data = $this->where('account_id', $pgId)
                ->select([
                    "account_id",
                    "label",
                ])
                ->first();
            if(isset($data)){
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function checkMetaActive($pgId)
    {
        try {
            return $this->where('account_id', $pgId)->where('is_active', true)->exists();
        }catch (QueryException $ex){
            report($ex);
            return false;
        }
    }

    public function getMetaById($pgId)
    {
        try {
            $data = $this->where('account_id', $pgId)->first();
            if(isset($data)) {
                return $data;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }

    public function getAllPgMeta() {
        try {
            $data = $this->select([
                "account_id",
                "label"
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

    public function proxyList(){
        return $this->belongsTo(ProxyList::class,'proxy_id','id');
    }

    public function validateFormData($formData, $accountId) {
        $validator = Validator::make($formData, [
            "label"             => "required",
            "email_id"          => "required",
            "debit_account"     => "required",
            "bank_code"         => "required"
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        if($this->where("account_id", $accountId)->where("debit_account", $formData['debit_account'])->exists()) {
            throw new \Exception("Meta Already Exists");
        }
    }

    public function getRenderConfig() {
        return [
            "show_columns" => [
                "id",
                "account_id",
                "label",
                "email_id",
                "merchant_id",
                "debit_account",
                "bank_code",
                "is_active",
                "min_limit",
                "max_limit",
                "max_count_limit"
            ],
            "editable_columns" => [
                "is_active",
                "min_limit",
                "max_limit",
                "max_count_limit",
            ],
            "add_meta_columns" => [
                "label",
                "email_id",
                "debit_account",
                "bank_code"
            ]
        ];
    }

    public function getDailyMaxCountLimitById($metaId)
    {
        try {
            $result = $this->where('account_id',$metaId)->where('is_active', true)->value('max_count_limit');
            if($result){
                return $result;
            }
            return 0;
        }catch (QueryException $ex){
            report($ex);
            return 0;
        }
    }

    public function getAccountHolderName($account_id)
    {
        try {
            $result = $this->where('account_id', $account_id)->value('label');
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            report($ex);
            return null;
        }
    }
}
