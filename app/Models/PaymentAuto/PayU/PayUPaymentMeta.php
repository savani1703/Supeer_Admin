<?php

namespace App\Models\PaymentAuto\PayU;

use App\Classes\Util\PgName;
use App\Models\Management\ProxyList;
use App\Traits\Encryptable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayUPaymentMeta extends Model
{
    use Encryptable;

    protected $connection = 'payment_auto';
    protected $table = 'tbl_payu_payment_meta';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'is_seamless' => 'boolean'
    ];

    protected $encryptable = [
        'salt',
    ];

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    private $defaultAccountId = 4001;

    public function getCreatedAtIstAttribute() {
        $createdAtOriginal = $this->created_at;
        if(isset($createdAtOriginal)) {
            return Carbon::parse($createdAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $createdAtOriginal;
    }

    public function getUpdatedAtIstAttribute() {
        $updatedAtOriginal = $this->last_update;
        if(isset($updatedAtOriginal)) {
            return Carbon::parse($updatedAtOriginal, "UTC")->setTimezone("Asia/Kolkata")->format("d-m-Y H:i:s");
        }
        return $updatedAtOriginal;
    }

    public function getMeta($filterData, $limit, $pageNo) {
        try {
            $meta = $this->newQuery();
            $meta->with(["proxyList"]);

            if(isset($filterData)) {
                if(isset($filterData['account_id']) && !empty($filterData['account_id'])) {
                    $meta->where("account_id",$filterData['account_id'] );
                }
                if(isset($filterData['label']) && !empty($filterData['label'])) {
                    $meta->where("label",$filterData['label'] );
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id'])) {
                    $meta->where("merchant_id",$filterData['merchant_id'] );
                }
            }
            $meta->select([
                "id",
                "account_id",
                "label",
                "product_info",
                "email_id",
                "merchant_id",
                "proxy_id",
                "bouncer_sub_domain_url",
                "callback_sub_domain_url",
                "is_active",
                "is_seamless",
                "min_limit",
                "max_limit",
                "turn_over",
                "current_turn_over",
                "available_method",
                "created_at",
                "last_update"
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
            if(isset($lastAccountId)) {
                return $lastAccountId + 1;
            }
            return $this->defaultAccountId;
        } catch (QueryException $ex) {
            throw $ex;
        }
    }

    public function addMeta($formData, $accountId) {
        try {
            $this->account_id = $accountId;
            $this->label = $formData['label'];
            $this->email_id = $formData['email_id'];
            $this->product_info = $formData['product_info'];
            $this->merchant_id = PgName::PAYU."_".strtoupper($formData['label']);
            $this->payu_key = $formData['payu_key'];
            $this->salt = $formData['salt'];
            $this->proxy_id = $formData['proxy_id'];
            $this->bouncer_sub_domain_url = $formData['bouncer_sub_domain_url'];
            $this->callback_sub_domain_url = $formData['callback_sub_domain_url'];
            $this->is_seamless = $formData['is_seamless'];
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

    public function updateMetaTurnOver($metaId, $turnOver) {
        try {
            if($this->where("account_id", $metaId)->update(["turn_over" => $turnOver])) {
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
            if($this->where("account_id", $metaId)->update(["available_method" => $method])) {
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

    public function updateMetaProductInfo($metaId, $productInfo) {
        try {
            if($this->where("account_id", $metaId)->update(["product_info" => $productInfo])) {
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
            $data = $this->where("account_id", $metaId)->value("label");
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
                    "account_id",
                    "label",
                    "is_seamless",
                    "merchant_id",
                    "available_method"
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
                "account_id",
                "label",
                "is_seamless",
                "merchant_id",
                "available_method"
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
            $data = $this->where("account_id", $metaId)
                ->select([
                    "account_id",
                    "label"
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
            $data = $this->where("account_id", $metaId)->first();
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

    public function proxyList(){
        return $this->belongsTo(ProxyList::class,'proxy_id','id');
    }

    public function validateFormData($formData, $accountId) {
        $validator = Validator::make($formData, [
            "label"                     => "required",
            "email_id"                  => "required",
            "product_info"              => "required",
            "payu_key"                  => "required",
            "salt"                      => "required",
            "proxy_id"                  => "required",
            "bouncer_sub_domain_url"    => "required|url",
            "callback_sub_domain_url"   => "required|url",
            "is_seamless"               => "required|in:0,1"
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        if($this->where("account_id", $accountId)->where("payu_key", $formData['payu_key'])->where("is_seamless",  $formData['is_seamless'])->exists()) {
            throw new \Exception("Meta Already Exists");
        }
    }

    public function getRenderConfig() {
        return [
            "show_columns" => [
                "id",
                "account_id",
                "label",
                "product_info",
                "email_id",
                "merchant_id",
                "proxy_id",
                "bouncer_sub_domain_url",
                "callback_sub_domain_url",
                "is_active",
                "is_seamless",
                "min_limit",
                "max_limit",
                "turn_over",
                "current_turn_over",
                "available_method",
            ],
            "editable_columns" => [
                "is_active",
                "min_limit",
                "max_limit",
                "turn_over",
                "available_method",
                "product_info",
            ],
            "add_meta_columns" => [
                "label",
                "email_id",
                "payu_key",
                "salt",
                "product_info",
                "proxy_id",
                "bouncer_sub_domain_url",
                "callback_sub_domain_url",
                "is_seamless"
            ]
        ];
    }
}
