<?php

namespace App\Classes\Util;

use App\Common\DateHelper;
use App\Jobs\DownloadManagerJob;
use App\Models\Management\AvailablePgMethod;
use App\Models\Management\BankTransactions;
use App\Models\Management\BlockInfo;
use App\Models\Management\CustomerLevel;
use App\Models\Management\MerchantPaymentMeta;
use App\Models\Management\MobileSync;
use App\Models\Management\Payout;
use App\Models\Management\PgMethod;
use App\Models\Management\PgRouter;
use App\Models\Management\SupportLogs;
use App\Models\Management\SupportReport;
use App\Models\Management\TransactionEvent;
use App\Models\Management\Transactions;
use App\Models\Management\WebhookRequest;
use App\Models\PaymentManual\AvailableBank;
use App\Models\PaymentManual\BankConfig;
use App\Models\PaymentManual\CustomerSuccessUpiMapping;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use App\Plugin\AccessControl\Utils\AccessModule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class SupportUtils
{

    public function getSupportLogs($filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $supportLogs = (new SupportLogs())->getSupportLogs($filterData, $limit, $pageNo);
            if(isset($supportLogs)) {
                $result = DigiPayUtil::withPaginate($supportLogs);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Logs Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getWebhookEvents($filterData, $limit, $pageNo) {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $webhookEvents = (new TransactionEvent())->getTransactionsEvent($filterData, $limit, $pageNo);
            if(isset($webhookEvents)) {
                $result = DigiPayUtil::withPaginate($webhookEvents);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Events Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPaymentMethods($filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $paymentMethods = (new PgMethod())->getPaymentMethod($filterData, $limit, $pageNo);
            if(isset($paymentMethods)) {
                $result = DigiPayUtil::withPaginate($paymentMethods);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payment Method Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addPaymentMethod($pgMethodId, $pgName, $metaCode, $methodName, $methodCode, $isSeamless, $hasSubMethod)
    {
        try {
            if(!(new AvailablePgMethod())->checkMethodIdIsExists($pgMethodId)) {
                $error['status'] = false;
                $error['message'] = "Invalid Method Id";
                return response()->json($error)->setStatusCode(400);
            }
            if((new PgMethod())->addPaymentMethod($pgMethodId, $pgName, $metaCode, $methodName, $methodCode, $isSeamless, $hasSubMethod)) {
                $result['status'] = true;
                $result['message'] = 'Payment Method Added';
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while add Payment Method";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addAvailableMethod($methodName, $methodIconUrl, $subMethodIconUrl)
    {
        try {
            $pgMethodId = strtoupper(str_replace(" ", "_", $methodName));
            if((new AvailablePgMethod())->checkMethodIdIsExists($pgMethodId)) {
                $error['status'] = false;
                $error['message'] = "Method Already Available";
                return response()->json($error)->setStatusCode(400);
            }
            if((new AvailablePgMethod())->addAvailableMethod($pgMethodId, $methodName, $methodIconUrl, $subMethodIconUrl)) {
                $result['status'] = true;
                $result['message'] = 'Payment Method Added';
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while add Payment Method";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getAvailableMethods()
    {
        try {
            $pgMethods = (new AvailablePgMethod())->getAvailableMethods();
            if(isset($pgMethods)) {
                $result['status'] = true;
                $result['message'] = 'Payment Method Retrieved';
                $result['data'] = $pgMethods;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while add Payment Method";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getCustomers($filterData, $pageNo, $limit)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $customerData = (new CustomerLevel())->getCustomers($filterData, $limit, $pageNo);
            if(isset($customerData)) {
                $pgRouterarray=array();
                $pgMetaarray=array();
                foreach ($customerData as &$dt)
                {
                    $dt->last_state=(new Transactions())->LastState($dt->merchant_id,$dt->customer_id);
                    $meta_info=(new MerchantPaymentMeta())->getMerchantMetaInfoByID($dt->last_meta_merchant_pay_id);
                    if(isset($meta_info)) {
                        if (isset($meta_info->pg_id) && isset($meta_info->pg_name)) {
                            $pgRouter = null;
                            if (array_key_exists($meta_info->pg_name, $pgRouterarray)) {
                                $pgRouter = $pgRouterarray[$meta_info->pg_name];
                            } else {
                                $pgRouter = (new PgRouter())->getRouterByPg($meta_info->pg_name);
                                $pgRouterarray[$meta_info->pg_name] = $pgRouter;
                            }
                            if (isset($pgRouter)) {
                                if (isset($pgRouter->payin_meta_router)) {
                                    $pgMeta = null;
                                    if (array_key_exists($meta_info->pg_id, $pgMetaarray)) {
                                        $pgMeta = $pgMetaarray[$meta_info->pg_id];
                                    } else {
                                        $pgMeta = (new $pgRouter->payin_meta_router)->getMetaForTransactionById($meta_info->pg_id);
                                        $pgMetaarray[$meta_info->pg_id] = $pgMeta;
                                    }
                                    if (isset($pgMeta)) {
                                        $dt->pg_label = $pgMeta->label;
                                    }
                                }
                            }
                        }
                    }
                }
                $result = DigiPayUtil::withPaginate($customerData);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Customers Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }
    public function getCustomersUpiMapDetailsById($customerId)
    {
        try {
             $customerData = (new CustomerSuccessUpiMapping())->getMapDetailsById($customerId);
            foreach ($customerData as $key => $customer_data) {
                $upi=$customer_data->success_upi_id;
                $success_upi_sum=(new BankTransactions())->getSuccessUpiSum($upi);
                $customerData[$key]["success_upi_sum"]=$success_upi_sum;
            }
            if(isset($customerData)) {
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['data'] = $customerData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Customers Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getCustomersStateDetailsById($customerId)
    {
        try {
            $customerData = null;
            $customerStateDetails = (new Transactions())->getCustomersStateDetailsById($customerId);
            if(isset($customerStateDetails) && !empty($customerStateDetails)) {
                foreach ($customerStateDetails as $_customerStateDetails) {
                    if (isset($_customerStateDetails->cust_state) && !empty($_customerStateDetails->cust_state)) {
                        $state = $_customerStateDetails->cust_state;
                        $totalInitialized   = (new Transactions())->getTransactionStateDetailsState($customerId, $state, PaymentStatus::INITIALIZED);
                        $totalProcessing    = (new Transactions())->getTransactionStateDetailsState($customerId, $state, PaymentStatus::PROCESSING);
                        $totalSuccess       = (new Transactions())->getTransactionStateDetailsState($customerId, $state, PaymentStatus::SUCCESS);
                        $totalFailed       = (new Transactions())->getTransactionStateDetailsState($customerId, $state, PaymentStatus::FAILED);

                       $totalTransaction = $totalInitialized->total_txn + $totalProcessing->total_txn + $totalSuccess->total_txn + $totalFailed->total_txn;

                        $customerData[$state] = [
                            'total_transaction' => $totalTransaction,
                            'total_processing' => $totalProcessing,
                            'total_initialized' => $totalInitialized,
                            'total_success' => $totalSuccess,
                            'total_failed' => $totalFailed,
                        ];
                    }
                }
            }
            if(isset($customerData)) {
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['data'] = $customerData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Customers Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateCustomerBlockStatus($customerId, $merchantId, $pgMethod, $status)
    {
//        dd($customerId, $merchantId, $pgMethod, $status)
        try {
            if((new CustomerLevel())->updateCustomerBlockStatus($customerId, $merchantId, $pgMethod, $status)) {
                $result['status'] = true;
                $result['message'] = 'Customer Block Status Updated';
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while update customer block status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPgWebhooks($filterData, $pageNo, $limit)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $pgWebhookData = (new WebhookRequest())->getPgWebhooks($filterData, $limit, $pageNo);
            if(isset($pgWebhookData)) {
                $result = DigiPayUtil::withPaginate($pgWebhookData);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "PG Webhook Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function generateReport($filterData, $reportType)
    {
        try {
            $filterData     = DigiPayUtil::parseFilterData($filterData);
            $downloadId     = 'BACKUP_'.DigiPayUtil::generateRandomString(25);
            $fId            = uniqid();
            $_filename      = $reportType."_{$fId}";
            $fileName       = Carbon::now()->format('dmY') . '/' . $_filename . '.xlsx';
            $currentTime    = Carbon::now()->format('dmY');
            $emailId        = DigiPayUtil::getAuthUser();
            $filterDataEn   = json_encode($filterData);

            if(strcmp($reportType,ReportType::TRANSACTION) === 0) {
                if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_REPORT)) {
                    return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
                $filterData["pg_type"] = AccessControlUtils::paymentPgType();
                $transactionCount = (new Transactions())->getTransactionDetailsForReport($filterData,true);
                if(!isset($transactionCount) || $transactionCount < 1){
                    return response()->json(['status' => false, 'message' => 'Transaction Not Found'])->setStatusCode(400);
                }
                $sha1Hash  = md5($reportType.$emailId.$transactionCount.$filterDataEn.$currentTime);
                return $this->addReportRequest($filterData, $reportType, $emailId, $transactionCount, $downloadId, $fileName, $sha1Hash);
            }

            if(strcmp($reportType,ReportType::PAYOUT) === 0){
                if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_REPORT)) {
                    return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
                $filterData["pg_type"] = AccessControlUtils::paymentPgType();
                $payoutCount = (new Payout())->getPayoutDetailsForReport($filterData, true);
                if(!isset($payoutCount) || $payoutCount < 1){
                    return response()->json(['status' => false, 'message' => 'Payout Not Found'])->setStatusCode(400);
                }
                $sha1Hash  = md5($reportType.$emailId.$payoutCount.$filterDataEn.$currentTime);
                return $this->addReportRequest($filterData, $reportType, $emailId, $payoutCount, $downloadId, $fileName, $sha1Hash);
            }

            if(strcmp($reportType,ReportType::BANK_TRANSACTION) === 0){
                if(!(new AccessControl())->hasAccessModule(AccessModule::BANK_TRANSACTION_REPORT)) {
                    return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
                $bankTransactionCount = (new BankTransactions())->getBankTransactionDetailsForReport($filterData, true);
                if(!isset($bankTransactionCount) || $bankTransactionCount < 1){
                    return response()->json(['status' => false, 'message' => 'Bank Transaction Count Not Found'])->setStatusCode(400);
                }
                $sha1Hash  = md5($reportType.$emailId.$bankTransactionCount.$filterDataEn.$currentTime);
                return $this->addReportRequest($filterData, $reportType, $emailId, $bankTransactionCount, $downloadId, $fileName, $sha1Hash);
            }

            if(strcmp($reportType,ReportType::BLOCK_INFO) === 0) {
                if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_REPORT)) {
                    return response()->json(['status' => false, 'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
                $blockInfoCount = (new BlockInfo())->getBlockInfoDetailsForReport($filterData, true);
                if(!isset($blockInfoCount) || $blockInfoCount < 1){
                    return response()->json(['status' => false, 'message' => 'Bank Transaction Count Not Found'])->setStatusCode(400);
                }
                $sha1Hash  = md5($reportType.$emailId.$blockInfoCount.$filterDataEn.$currentTime);
                return $this->addReportRequest($filterData, $reportType, $emailId, $blockInfoCount, $downloadId, $fileName, $sha1Hash);
            }
            return response()->json(['status' => false, 'message' => 'Invalid Report Data'])->setStatusCode(400);
        } catch (\Exception $ex) {
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    private function addReportRequest($filterData, $reportType, $emailId, $count, $downloadId, $fileName, $sha1Hash)
    {
        try{
            $downloadDetails = (new SupportReport())->getDownloadByHash($sha1Hash);

            if(isset($downloadDetails) && !empty($downloadDetails)){
                if($downloadDetails->IsExpire === false){
                    return response()->json(['status' => false, 'message' => 'Duplicate Report Not Allowed'])->setStatusCode(400);
                }
            }

            $result = (new SupportReport())->addRecord($reportType, $emailId, $count, $downloadId, $fileName, $sha1Hash);
            if($result === false){
                return response()->json(['status' => false, 'message' => 'Failed to Report Generate'])->setStatusCode(400);
            }

            $job = (new DownloadManagerJob($filterData, $reportType, $emailId, $count, $downloadId, $fileName))->onQueue('support_download_job');
            dispatch($job);
            return response()->json(['status' => true, 'message' => 'Your report in current queue'])->setStatusCode(200);

        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    public function getGeneratedReport($limit, $pageNo)
    {
        try{
            $reports = (new SupportReport())->getDownloadReportDetails($limit, $pageNo);
            if(isset($reports)) {
                $result = DigiPayUtil::withPaginate($reports);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Reports Not found";
            return response()->json($error)->setStatusCode(400);
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    public static function logs($action, $actionDetails) {
        $emailId = DigiPayUtil::getAuthUser();
        (new SupportLogs())->addLogsDetails($emailId, $action, $actionDetails);
    }

    public function getBlockInfo($filterData, $limit, $pageNo)
    {
        try{
            $blockInfo = (new BlockInfo())->getBlockInfoData($filterData, $limit, $pageNo);
            if(isset($blockInfo)) {
                $result = DigiPayUtil::withPaginate($blockInfo);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    public function deleteBlockInfo($blockData)
    {
        try{
            if((new BlockInfo())->deleteBlockData($blockData)) {
                SupportUtils::logs("BLOCK_DATA", "Block Data Deleted, Block_Data: $blockData");
                $result['status'] = true;
                $result['message'] = "Data Deleted";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    public function getBankSync()
    {
        try {
            $today = Carbon::now()->format("Y-m-d");
            $data = (new AvailableBank())->getTodaySyncBank($today);
            $activemetas=array();
            $deactivemetas=array();
            $totalbal=0;
            $fmt = numfmt_create( 'en_IN', NumberFormatter::DECIMAL);
            $is_allowed= (new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_SUMMARY);
            $bankConfig = (new BankConfig())->getBankConfig();
            foreach ($data as &$dt)
            {
                $note = "Bank Up";
                if($dt->bank_name){
                    $bankConDt = $bankConfig->where("bank_name", "=", $dt->bank_name)->value('is_down');
                    if($bankConDt){
                        $note = "Bank Down";
                    }
                }
                $dt->note = $note;

                $activemeta= (new MerchantPaymentMeta())->is_bank_Active($dt->av_bank_id);
                if(isset($activemeta)) {
                    $dt->is_sync_active = true;
                    $totalbal=$totalbal+ $dt->live_bank_balance;
                    if($is_allowed) {
                        $dt->turnover = numfmt_format_currency($fmt, round($activemeta->current_turnover), "INR");
                        $dt->daily_limit = numfmt_format_currency($fmt, round($activemeta->daily_limit), "INR");
                        $dt->daily_limit_per =round(($activemeta->current_turnover*100)/ $activemeta->daily_limit,2);
                        $dt->is_level1 = $activemeta->is_level1;
                        $dt->merchant_rid = $activemeta->merchant_id;
                    }else
                    {
                        $dt->turnover=0;
                        $dt->daily_limit=0;
                        $dt->daily_limit_per=0;
                    }
                    $dt->live_bank_balance_int =round($dt->live_bank_balance);
                    $dt->live_bank_balance =numfmt_format_currency($fmt,round($dt->live_bank_balance), "INR");
                    $dt->last_success =(new Transactions())->LastSuccessDateByMetaId($dt->av_bank_id);
                    $dt->last_success_ago =DateHelper::diffForHumans((new Transactions())->LastSuccessUTCDateByMetaId($dt->av_bank_id));
                    $dt->last_success_mindeff_ist =(new Transactions())->LastSuccessMinDiffDateByMetaId($dt->av_bank_id);
                    $activemetas[]=$dt;
                }
               /* else
                {
                    $activemeta= (new MerchantPaymentMeta())->getLastMetaDetails($dt->av_bank_id);
                    if(isset($activemeta)) {
                        $dt->is_sync_active = false;
                        $totalbal=$totalbal+ $dt->live_bank_balance;
                        if($is_allowed) {
                            $dt->turnover = numfmt_format_currency($fmt, round($activemeta->current_turnover), "INR");
                            $dt->daily_limit = numfmt_format_currency($fmt, round($activemeta->daily_limit), "INR");
                            $dt->daily_limit_per =round( ($activemeta->current_turnover*100)/ $activemeta->daily_limit,2);
                        }else
                        {
                            $dt->turnover=0;
                            $dt->daily_limit=0;
                            $dt->daily_limit_per=0;
                        }
                        $dt->live_bank_balance =numfmt_format_currency($fmt, round($dt->live_bank_balance), "INR");
                        $dt->last_success =(new Transactions())->LastSuccessDateByMetaId($dt->av_bank_id);
                        $dt->last_success_ago =DateHelper::diffForHumans((new Transactions())->LastSuccessUTCDateByMetaId($dt->av_bank_id));
                        $dt->last_success_mindeff_ist =(new Transactions())->LastSuccessMinDiffDateByMetaId($dt->av_bank_id);
                        $deactivemetas[]=$dt;
                    }
                }*/
            }
          /*  usort($activemetas, function ($a, $b) {
                return strcmp($b->live_bank_balance_int, $a->live_bank_balance_int);
            });*/

            foreach ($deactivemetas as $metadt)
          {
              $activemetas[]=$metadt;
          }

            if(isset($activemetas)) {
                $result['status'] = true;
                $result['message'] = "Data Retrieved";
                $result['bank_bal'] = numfmt_format_currency($fmt, round($totalbal), "INR");
                $result['data'] = $activemetas;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $result['bank_bal'] = 0;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }
    public function getMobileSync()
    {
        try {
            $today = Carbon::now()->subDay()->toDateTimeString();
            $data = (new MobileSync())->where('last_sync_date','>',$today)->get();

            foreach ($data as &$dt) {
                    $dt->last_success_mindeff_ist =DateHelper::diffForHumans(Carbon::parse($dt->last_sync_date, "UTC"));
                    $dt->last_success_mindeff_ist_ago =Carbon::now()->diffInMinutes(Carbon::parse($dt->last_sync_date, "UTC"));
                    $dt->created_at_ist =Carbon::parse($dt->created_at, "UTC")->setTimezone("Asia/Kolkata")->toDateTimeString();
                    $dt->last_sync_date_ist =Carbon::parse($dt->last_sync_date, "UTC")->setTimezone("Asia/Kolkata")->toDateTimeString();
            }
            if(isset($data)) {
                $result['status'] = true;
                $result['message'] = "Data Retrieved";
                $result['data'] = $data;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $result['bank_bal'] = 0;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }
}
