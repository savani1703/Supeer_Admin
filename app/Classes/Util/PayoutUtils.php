<?php

namespace App\Classes\Util;

use App\Models\Management\MerchantBalance;
use App\Models\Management\Payout;
use App\Models\Management\PayoutConfig;
use App\Models\Management\PayoutCustomerLevel;
use App\Models\Management\PgRouter;
use App\Models\Management\Transactions;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PayoutUtils
{

    public function getPayout($filterData, $pgType, $limit, $pageNo) {
        try {

            $filterData = DigiPayUtil::parseFilterData($filterData);
            $payoutSummary=null;
             if((new AccessControl())->hasAccessModule(AccessModule::PAYOUT_SUMMARY)) {
                 $payoutSummary = (new Payout())->getPayoutSummary($filterData, $pgType);
             }
            $payout = (new Payout())->getPayout($filterData, $pgType, $limit, $pageNo);

            if(isset($payout)) {
                $payoutData = $this->parseWithPgLable($payout->items());
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['current_page'] = $payout->currentPage();
                $result['last_page'] = $payout->lastPage();
                $result['is_last_page'] = !$payout->hasMorePages();
                $result['total_item'] = $payout->total();
                $result['current_item_count'] = $payout->count();
                $result['data'] = $payoutData;
                $result['payout_summary'] = $payoutSummary;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "payout  Not found";
            $error['summary'] = [
                "total_payout" => 0,
                "payout_amount" => 0,
                "total_payout_fees" => 0,
                "total_associate_fees" => 0,
                "total_payout_amount" => 0,
            ];
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while get payout";
            $error['summary'] = [
                "total_payout" => 0,
                "payout_amount" => 0,
                "total_payout_fees" => 0,
                "total_associate_fees" => 0,
                "total_payout_amount" => 0,
            ];
            return response()->json($error)->setStatusCode(400);
        }
    }

    public static function CommanFilter($payout, $filterData, $pgType) {
        try {
            if(strcmp(strtolower($pgType), "all") !== 0) {
                $payout->where('pg_type', $pgType);
            }
            if(isset($filterData) && sizeof($filterData) > 0) {

                if(isset($filterData['payout_id']) && !empty($filterData['payout_id'])) {
                    $payout->where('payout_id', $filterData['payout_id']);
                }
                if(isset($filterData['merchant_ref_id']) && !empty($filterData['merchant_ref_id'])) {
                    $payout->where('merchant_ref_id', $filterData['merchant_ref_id']);
                }
                if(isset($filterData['customer_id']) && !empty($filterData['customer_id'])) {
                    $payout->where('customer_id', $filterData['customer_id']);
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id']) && strcmp($filterData['merchant_id'], "All") !== 0) {
                    $payout->where('merchant_id', $filterData['merchant_id']);
                }
                if(isset($filterData['customer_email']) && !empty($filterData['customer_email'])) {
                    $payout->where('customer_email', $filterData['customer_email']);
                }
                if(isset($filterData['customer_mobile']) && !empty($filterData['customer_mobile'])) {
                    $payout->where('customer_mobile', $filterData['customer_mobile']);
                }
                if(isset($filterData['customer_name']) && !empty($filterData['customer_name'])) {
                    $payout->where('customer_name', $filterData['customer_name']);
                }
                if(isset($filterData['temp_bank_rrn']) && !empty($filterData['temp_bank_rrn'])) {
                    $payout->where('temp_bank_rrn', $filterData['temp_bank_rrn']);
                }
                if(isset($filterData['process_by']) && !empty($filterData['process_by'])) {
                    $payout->where('process_by', $filterData['process_by']);
                }

                if(isset($filterData['payout_amount']) && !empty($filterData['payout_amount'])) {
                    $payout->where('payout_amount', $filterData['payout_amount']);
                }
                if(isset($filterData['pg_name']) && !empty($filterData['pg_name'])) {
                    if(strcmp($filterData['pg_name'], "All") !== 0) {
                        $payout->where('pg_name', $filterData['pg_name']);
                    }
                }
                if(isset($filterData['payout_type']) && !empty($filterData['payout_type'])) {
                    if(strcmp($filterData['payout_type'], "All") !== 0) {
                        $payout->where('payout_type', $filterData['payout_type']);
                    }
                }
                if(isset($filterData['meta_id']) && !empty($filterData['meta_id'])) {
                    if(strcmp($filterData['meta_id'], "All") !== 0) {
                        $payout->where('meta_id', $filterData['meta_id']);
                    }
                }
                if(isset($filterData['account_holder_name']) && !empty($filterData['account_holder_name'])) {
                    $payout->where('account_holder_name', $filterData['account_holder_name']);
                }
                if(isset($filterData['bank_account']) && !empty($filterData['bank_account'])) {
                    $payout->where('bank_account', $filterData['bank_account']);
                }
                if(isset($filterData['udf1']) && !empty($filterData['udf1'])) {
                    $payout->where('udf1', $filterData['udf1']);
                }
                if(isset($filterData['udf2']) && !empty($filterData['udf2'])) {
                    $payout->where('udf2', $filterData['udf2']);
                }
                if(isset($filterData['udf3']) && !empty($filterData['udf3'])) {
                    $payout->where('udf3', $filterData['udf3']);
                }
                if(isset($filterData['udf4']) && !empty($filterData['udf4'])) {
                    $payout->where('udf4', $filterData['udf4']);
                }
                if(isset($filterData['udf5']) && !empty($filterData['udf5'])) {
                    $payout->where('udf5', $filterData['udf5']);
                }
                if(isset($filterData['bank_rrn']) && !empty($filterData['bank_rrn'])) {
                    $payout->where('bank_rrn', $filterData['bank_rrn']);
                }
                if(isset($filterData['pg_ref_id']) && !empty($filterData['pg_ref_id'])) {
                    $payout->where('pg_ref_id', $filterData['pg_ref_id']);
                }
                if(isset($filterData['manual_pay_batch_id']) && !empty($filterData['manual_pay_batch_id'])) {
                    $payout->where('manual_pay_batch_id', $filterData['manual_pay_batch_id']);
                }
                if(isset($filterData['status']) && !empty($filterData['status']) && strcmp($filterData['status'], "All") !== 0) {
                    $payout->where('payout_status', $filterData['status']);
                }
                if(isset($filterData['min_amount']) && !empty($filterData['min_amount'] && $filterData['min_amount'] > 0 ) && isset($filterData['max_amount']) && !empty($filterData['max_amount']) && $filterData['max_amount'] > 0 ) {
                    $payout->where('payout_amount', '>=', $filterData['min_amount']);
                    $payout->where('payout_amount', '<=', $filterData['max_amount']);
                }
                if(isset($filterData['success_start_date']) && !empty($filterData['success_start_date']) && isset($filterData['success_end_date']) && !empty($filterData['success_end_date'])) {
                    $payout->whereBetween('success_at', [$filterData['success_start_date'], $filterData['success_end_date']]);
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $payout->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }

            }
            return $payout;
        } catch (\Exception $ex) {
            return $payout;
        }
    }

    public static function SelectById() {
        return [
            'payout_id',
            'merchant_ref_id',
            'merchant_id',
            'payout_amount',
            'payout_fees',
            'associate_fees',
            'total_amount',
            'payout_currency',
            'payout_type',
            'customer_id',
            'customer_name',
            'customer_email',
            'customer_mobile',
            'payout_count',
            'total_payout_amount',
            'account_holder_name',
            'bank_account',
            'ifsc_code',
            'vpa_address',
            'bank_name',
            'payout_status',
            'internal_status',
            'pg_ref_id',
            'pg_response_msg',
            'payout_count',
            'total_payout_amount',
            'pg_res',
            'bank_rrn',
            'temp_bank_rrn',
            'pg_payout_date',
            'is_webhook_called',
            'manual_pay_batch_id',
            'payout_by',
            'process_by',
            'pg_name',
            'meta_id',
            'customer_ip',
            'udf1',
            'udf2',
            'udf3',
            'udf4',
            'udf5',
            'created_at',
            'updated_at',
            'success_at',
        ];
    }

    public function getPayoutById($payoutId) {
        try {
            $payoutData = (new Payout())->getpayoutById($payoutId, self::SelectById());
            if(isset($payoutData)) {
                $result['status'] = true;
                $result['message'] = 'Payout  Details (ById) Retrieve successfully';
                $result['data'] = $payoutData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout  Details (ById) Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Payout  Details (ById) ";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function resendWebhook($payoutId) {
        try {
            $payoutData = (new Payout())->markAsResendWebhook($payoutId);
            if($payoutData) {
                SupportUtils::logs('PAYOUT',"Webhook Resend, PAYOUT_ID: $payoutId");
                $result['status'] = true;
                $result['message'] = "$payoutId: Webhook Resend successfully";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while resend Payout Webhook";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function cancelledInitializedPayout($payoutId) {
        try {
            $payoutData = (new Payout())->cancelledInitializedPayout($payoutId);
            if($payoutData) {
                SupportUtils::logs('PAYOUT',"Payout Cancelled, PAYOUT_ID: $payoutId");
                $result['status'] = true;
                $result['message'] = "$payoutId: Cancelled";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while Cancelled Payout";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPayoutConfiguration() {
        try {
            $payoutConfig = (new PayoutConfig())->loadConfig();
            if(isset($payoutConfig)) {
                $result['status'] = true;
                $result['message'] = 'Payout Configuration Retrieve successfully';
                $result['data'] = $payoutConfig;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Configuration Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Payout Configuration";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function resetLowBalPayoutToInitialize() {
        try {
            $payoutConfig = (new Payout())->resetLowBalPayoutToInitialize();
            if(isset($payoutConfig)) {
                SupportUtils::logs('PAYOUT',"LowBal Status Reset");
                $result['status'] = true;
                $result['message'] = 'Payout LowBal Reset successfully';
                $result['data'] = $payoutConfig;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout LowBal Reset Failed";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while Reset Payout LowBal";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutConfiguration(
        $isAutoTransferEnable,
        $isPayoutStatusCallEnable,
        $maxManualTransferLimit,
        $minManualTransferLimit,
        $maxLowbalLimit,
        $maxPendingLimit,
        $maxLastFailedLimit,
        $minAutoTransferLimit,
        $maxAutoTransferLimit,
        $payout_delayed_in_seconds,
        $small_first,
        $large_first,
        $is_auto_level_active,
    )
    {
        try {

            $updateData = [
                'is_auto_transfer_enable' => $isAutoTransferEnable,
                'is_payout_status_call_enable' => $isPayoutStatusCallEnable,
                'max_manual_transfer_limit' => $maxManualTransferLimit,
                'min_manual_transfer_limit' => $minManualTransferLimit,
                'max_lowbal_limit' => $maxLowbalLimit,
                'max_pending_limit' => $maxPendingLimit,
                'max_last_failed_limit' => $maxLastFailedLimit,
                'min_auto_transfer_limit' => $minAutoTransferLimit,
                'max_auto_transfer_limit' => $maxAutoTransferLimit,
                'payout_delayed_in_seconds' => $payout_delayed_in_seconds,
                'small_first' => $small_first,
                'large_first' => $large_first,
                'is_auto_level_active' => $is_auto_level_active,
            ];
            $payoutConfig = (new PayoutConfig())->loadConfig();
            if((new PayoutConfig())->updateConfig($payoutConfig->id, $updateData)) {
                $logData = json_encode($updateData);
                SupportUtils::logs('PAYOUT',"Payout Config Updated, Update Data: $logData");
                $result['status'] = true;
                $result['message'] = 'Payout Configuration Update successfully';
                $result['data'] = $payoutConfig;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Configuration Update Failed";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while Update Payout Configuration";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function parseWithPgLable($payouts)
    {
        try {
            if(isset($payouts)) {
                foreach ($payouts as $key => $payout) {
                    if(isset($payout->meta_id) && isset($payout->pg_name)) {
                        $pgRouter = (new PgRouter())->getRouterByPg($payout->pg_name);
                        if(isset($pgRouter)) {
                            if(isset($pgRouter->payout_meta_router)) {
                                $pgMeta = (new $pgRouter->payout_meta_router)->getMetaForPayoutByMetaId($payout->meta_id);
                                if(isset($pgMeta)) {
                                    $payouts[$key]['pg_label'] = $pgMeta->label;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {

        }
        return $payouts;
    }

    public function ResetInitializedPayout($payoutId)
    {
        try {
            $payoutData = (new Payout())->ResetInitializedPayout($payoutId);
            if($payoutData) {
                SupportUtils::logs('PAYOUT',"Payout Initialized, PAYOUT_ID: $payoutId");
                $result['status'] = true;
                $result['message'] = "$payoutId: Initialized";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while Initialized Payout";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getDashboardSummary()
    {
        $dashboardSummary['Payout LowBal Count'] = 0;
        $dashboardSummary['Payout Initialized Count'] = 0;
        $dashboardSummary['Payout Initialized Amount'] = 0;
        $dashboardSummary['Payin Last Success'] = 0;
        $dashboardSummary['Payout Last Success'] = 0;
        try {
            $merchantBalance = (new Payout())->getSummary();
            $lowbal = (new Payout())->getLowBalCount();
            $lastPayinSuccess = (new Transactions())->LastSuccessDate();
            $lastPayoutSuccess = (new Payout())->LastSuccessDate();
            if(isset($merchantBalance)) {
                $dashboardSummary['Payout LowBal Count'] = $lowbal;
                $dashboardSummary['Payout Initialized Amount'] = round($merchantBalance->total_payout_amount, 2);
                $dashboardSummary['Payout Initialized Count'] = round($merchantBalance->total_pending, 0);
            }
            $dashboardSummary['Payin Last Success'] = $lastPayinSuccess;
            $dashboardSummary['Payout Last Success'] = $lastPayoutSuccess;
        } catch (\Exception $ex) {
            Log::error('Error Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
        $result['status'] = true;
        $result['message'] = "Merchant Management Dashboard Summary Retrieved";
        $result['data'] = $dashboardSummary;
        return response()->json($result)->setStatusCode(200);
    }

    public function getPayoutPgMeta($pg_name)
    {
        try {

            $availablePayoutMeta = [];
            $pgRouter = (new PgRouter())->getPayoutPgRoute($pg_name);
            if(isset($pgRouter) && !empty($pgRouter)){
                if(isset($pgRouter->payout_meta_router)) {
                    $payoutMeta = (new $pgRouter->payout_meta_router)->getAllActivePgMeta();
                    if(isset($payoutMeta)) {
                        foreach ($payoutMeta as $_payoutMeta) {
                            $availablePayoutMeta[] = [
                                "account_id" => $_payoutMeta->account_id,
                                "label" => $_payoutMeta->label,
                            ];
                        }
                    }
                }
            }

            if(sizeof($availablePayoutMeta) > 0) {
                $result['status'] = true;
                $result['message'] = 'Payout Pg Meta Retrieve successfully';
                $result['data'] = $availablePayoutMeta;
                return response()->json($result)->setStatusCode(200);
            }

            $result['status'] = true;
            $result['message'] = 'Payout Pg Meta Retrieve not found';
            $result['data'] = $availablePayoutMeta;
            return response()->json($result)->setStatusCode(400);
        }catch (\Exception $ex){
            $error['status'] = false;
            $error['message'] = "Error while get Payout PG Meta";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function payoutStatusUpdate($payoutId, $payoutStatus, $payoutUtr){
        try {
            $emailId = DigiPayUtil::getAuthUser();
            $result = (new Payout())->payoutStatusUpdate($payoutId, $payoutStatus, $payoutUtr, $emailId);
            if($result){
                $error['status'] = true;
                $error['message'] = 'payout status update successfully';
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = 'payout status update failed';
            return response()->json($result)->setStatusCode(400);

        }catch (\Exception $ex){
            $error['status'] = false;
            $error['message'] = "Error while get Payout PG Meta";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getCustLevelData($filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $levelData = (new PayoutCustomerLevel())->getCustLevelData($filterData, $limit, $pageNo);
                if(isset($levelData)) {
                    $payoutData = $this->parseWithPgLable($levelData->items());
                    $result['status'] = true;
                    $result['message'] = 'Data Retrieve successfully';
                    $result['current_page'] = $levelData->currentPage();
                    $result['last_page'] = $levelData->lastPage();
                    $result['is_last_page'] = !$levelData->hasMorePages();
                    $result['total_item'] = $levelData->total();
                    $result['current_item_count'] = $levelData->count();
                    $result['data'] = $payoutData;
                    return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPayoutAccountLoad()
    {
        try {
            $payoutDetails = (new Payout())->getPayoutLoadOnlyFp();
            if(!isset($payoutDetails) || empty($payoutDetails)){
                $error['status'] = false;
                $error['message'] = "Data Not found";
                return response()->json($error)->setStatusCode(400);
            }

            $payoutAccountLoad = array();
            foreach ($payoutDetails as $_payoutDetails){
                if(isset($_payoutDetails->customer_id) && !empty($_payoutDetails->customer_id)){
                    $payoutLevel = (new PayoutCustomerLevel())->getCustLevelDataById($_payoutDetails->customer_id);
                    if(isset($payoutLevel) && !empty($payoutLevel)){
                        if(isset($payoutLevel->meta_id) && !empty($payoutLevel->meta_id)){
                            if(array_key_exists($payoutLevel->meta_id, $payoutAccountLoad)){
                                $totalLoad = $payoutAccountLoad[$payoutLevel->meta_id]['total_load'];
                                $totalCount = $payoutAccountLoad[$payoutLevel->meta_id]['total_count'];
                                $payoutAccountLoad[$payoutLevel->meta_id]['total_load'] = $totalLoad + $_payoutDetails->payout_amount;
                                $payoutAccountLoad[$payoutLevel->meta_id]['total_count'] = $totalCount + 1;
                            }else{
                                $label = null;
                                $pgRouter = (new PgRouter())->getRouterByPg($payoutLevel->pg_name);
                                if(isset($pgRouter)) {
                                    if(isset($pgRouter->payout_meta_router)) {
                                        $pgMeta = (new $pgRouter->payout_meta_router)->getMetaForPayoutByMetaId($payoutLevel->meta_id);
                                        if(isset($pgMeta)) {
                                            $label = $pgMeta->label;
                                        }
                                    }
                                }
                                $payoutAccountLoad[$payoutLevel->meta_id] = [
                                    'meta_id' => $payoutLevel->meta_id,
                                    'pg_name' => $payoutLevel->pg_name,
                                    'total_load' => $_payoutDetails->payout_amount,
                                    'total_count' => 1,
                                    'label' => $label,
                                ];
                            }
                        }
                    }else{
                        if(array_key_exists("OTHER", $payoutAccountLoad)){
                            $totalLoad = $payoutAccountLoad["OTHER"]['total_load'];
                            $totalCount = $payoutAccountLoad["OTHER"]['total_count'];
                            $payoutAccountLoad["OTHER"]['total_load'] = $totalLoad + $_payoutDetails->payout_amount;
                            $payoutAccountLoad["OTHER"]['total_count'] = $totalCount + 1;
                        }else{
                            $payoutAccountLoad["OTHER"] = [
                                'meta_id' => "OTHER",
                                'pg_name' => "OTHER",
                                'total_load' => $_payoutDetails->payout_amount,
                                'total_count' => 1,
                                'label' => "OTHER",
                            ];
                        }
                    }
                }
            }

            if(count($payoutAccountLoad) > 0){
                $error['status'] = true;
                $error['message'] = "details retried successfully";
                $error['data'] = $payoutAccountLoad;
                return response()->json($error)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function setCustomerPayoutLevelData($payout, $updatedBy){
        try {
            $pgName = null;
            $bankName = null;
            $metaId = null;
            $lastMetaMerchantId = null;

            $lastSuccessPayout = (new Payout())->getLastSuccessRec($payout->customer_id);
            if (isset($lastSuccessPayout) && !empty($lastSuccessPayout)) {
                $pgName = $lastSuccessPayout->pg_name;
                $bankName = $lastSuccessPayout->bank_name;
                $metaId = $lastSuccessPayout->meta_id;
                $lastMetaMerchantId = $lastSuccessPayout->meta_merchant_id;
            }

            if ($metaId && $pgName) {
                $accountNumber = $payout->bank_account;
                $accountHolderName = $payout->account_holder_name;
                $ifscCode = $payout->ifsc_code;
                $customerId = $payout->customer_id;
                $lastSuccessAt = $lastSuccessPayout->success_at ? $lastSuccessPayout->success_at : null;

                $result = (new PayoutCustomerLevel())->addCustomerPayoutLevelDetails($customerId, $accountNumber, $accountHolderName, $bankName, $ifscCode, $pgName, $metaId, $lastMetaMerchantId, $lastSuccessAt, $updatedBy);
                if ($result) {
                    echo "\n added successfully account number : " . $accountNumber . " customerId : " . $customerId;
                } else {
                    echo "\n Skipped..... " . $accountNumber . "customerId : " . $customerId;
                }
            }
        }catch (\Exception $ex){

        }
    }

    public function setCustomerPayoutLevelDataV1($payout, $updatedBy){
        try {

            $pgName = null;
            $metaId = null;
            $lastMetaMerchantId = null;
            $lastSuccessAt = null;

            $lastSuccessPayout = (new Payout())->getLastSuccessRec($payout->customer_id);
            if (isset($lastSuccessPayout) && !empty($lastSuccessPayout)) {
                $pgName = $lastSuccessPayout->pg_name;
                $metaId = $lastSuccessPayout->meta_id;
                $lastMetaMerchantId = $lastSuccessPayout->meta_merchant_id;
                $lastSuccessAt = $lastSuccessPayout->success_at ? $lastSuccessPayout->success_at : null;
            }

            if($payout->meta_id !== $metaId){
                echo "\n !!!!!!!!!!!!! ". $payout->customer_id ." !!!!!!!!!!!!!";
            }

            if ($metaId && $pgName) {
                $customerId = $payout->customer_id;
                $result = (new PayoutCustomerLevel())->updateCustomerPayoutLevelDetails($customerId, $pgName, $metaId, $lastMetaMerchantId, $lastSuccessAt, $updatedBy);
                if ($result) {
                    echo "\n updated successfully  customerId : " . $customerId;
                } else {
                    echo "\n Skipped.....  customerId : " . $customerId;
                }
            }
        }catch (\Exception $ex){

        }
    }

}
