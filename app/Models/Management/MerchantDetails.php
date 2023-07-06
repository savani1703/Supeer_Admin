<?php

namespace App\Models\Management;

use App\Classes\Util\AccountStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;

class MerchantDetails extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_merchant_details';
    protected $primaryKey = 'merchant_email';
    public $incrementing = false;
    public $timestamps = false;

    protected $appends = [
        "created_at_ist",
        "updated_at_ist",
    ];

    protected $casts = [
        "is_auto_approved_payout" => "boolean",
        "is_password_temp" => "boolean",
        "is_payin_enable" => "boolean",
        "is_payout_enable" => "boolean",
        "have_customer_details" => "boolean",
        "have_customer_details_in_api" => "boolean",
        "is_required_payment_failed_webhook" => "boolean",
        "is_enable_browser_check" => "boolean",
        "is_balance_check_enable" => "boolean",
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

    public function addMerchant($merchantId, $publicKey, $merchantEmail, $password, $merchantName) {
        try {
            $this->merchant_email = $merchantEmail;
            $this->merchant_id = $merchantId;
            $this->merchant_key = $publicKey;
            $this->merchant_name = $merchantName;
            $this->password = $password;
            $this->account_status = AccountStatus::New;
            $this->is_password_temp = true;
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
            return null;
        }
    }


    public function getMerchants($filterData, $limit, $pageNo) {
        try {
            $merchants = $this->newQuery();

            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['merchant_id'])) {
                    $merchants->where("merchant_id", $filterData['merchant_id']);
                }
            }
            $merchants->select([
                'merchant_email',
                'merchant_id',
                'merchant_name',
                'account_status',
                'is_payin_enable',
                'is_payout_enable',
                'is_dashboard_payout_enable',
                'pay_in_auto_fees',
                'pay_in_manual_fees',
                'payout_fees',
                'payin_associate_fees',
                'payout_associate_fees',
                'settlement_cycle',
                'payout_delayed_time',
                'is_auto_approved_payout',
                'min_transaction_limit',
                'max_transaction_limit',
                'have_customer_details',
                'have_customer_details_in_api',
                'is_required_payment_failed_webhook',
                'is_enable_browser_check',
                'is_balance_check_enable',
                'is_settlement_enable',
                'old_users_days',
                'checkout_color',
                'webhook_url',
                'payout_webhook_url',
                'checkout_theme_url',
                'merchant_bouncer_url',
                'created_at',
                'updated_at',
            ]);
            Paginator::currentPageResolver(function () use ($pageNo) {
                return $pageNo;
            });
            $merchants->orderBy('created_at', 'desc');
            if($merchants->count() > 0){
                return $merchants->paginate($limit);
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

    public function updateMerchantData($merchantId, $updateData) {
        try {
            return $this->where("merchant_id", $merchantId)->update($updateData);
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

    public function checkMerchantEmail($merchantEmail)
    {
        try {
            $result = $this->where('merchant_email',$merchantEmail)->exists();
            if($result){
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
            return true;
        }
    }

    public function checkMerchantId($merchantId)
    {
        try {
            $result = $this->where('merchant_id',$merchantId)->exists();
            if($result){
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
            return true;
        }
    }

    public function getMerchantDetails($merchantId)
    {
        try {
            $result = $this->where('merchant_id', $merchantId)->first();
            if(isset($result)){
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

    public function getMerchantList()
    {
        try {
            $result = $this->select(["merchant_id", "merchant_name"])->get()->toArray();
            if(sizeof($result) > 0){
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

    public function renderConfig() {
        return [
            "editable_columns" => [
                "account_status",
                "webhook_url",
                "payout_webhook_url",
                "is_payin_enable",
                "is_payout_enable",
                "is_dashboard_payout_enable",
                "pay_in_auto_fees",
                "pay_in_manual_fees",
                "payin_associate_fees",
                "payout_fees",
                "payout_associate_fees",
                "settlement_cycle",
                "is_settlement_enable",
                "payout_delayed_time",
                "is_auto_approved_payout",
                "min_transaction_limit",
                "max_transaction_limit",
                "have_customer_details",
                "have_customer_details_in_api",
                "is_required_payment_failed_webhook",
                "is_enable_browser_check",
                "is_balance_check_enable",
                "old_users_days",
                "checkout_color",
                "checkout_theme_url"
            ],
        ];
    }

}
