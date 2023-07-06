<?php

namespace App\Classes\Util;

use App\Models\Management\BankTransactions;
use App\Models\Management\MerchantBalance;
use App\Models\Management\PaymentTurnover;
use App\Models\Management\Payout;
use App\Models\Management\PayoutTurnover;
use App\Models\Management\PgRouter;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\PayoutCrData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use NumberFormatter;

class DashboardUtils
{

    public function getMMDashboardSummary()
    {
        $dashboardSummary['Today\'s Total Collection'] = 0;
        $dashboardSummary['Today\'s Total Payout'] = 0;
        $dashboardSummary['Total Payout Balance'] = 0;
        $dashboardSummary['Bank Unclaimed Balance'] = 0;

        try {
            $startDate = Carbon::now("Asia/Kolkata")->format("Y-m-d");
            $merchantBalance = (new MerchantBalance())->getMerchantBalanceSummary($startDate);
            $unclaimed = (new BankTransactions())->getUncalimedBal();
            $fmt = numfmt_create( 'en_IN', NumberFormatter::DECIMAL);
            if(isset($merchantBalance)) {
                $dashboardSummary['Today\'s Total Collection'] =numfmt_format_currency($fmt, round($merchantBalance->total_collection, 2), "INR");
                $dashboardSummary['Today\'s Total Payout'] = numfmt_format_currency($fmt,round($merchantBalance->total_payout, 2), "INR");
                $dashboardSummary['Total Payout Balance'] = numfmt_format_currency($fmt,round($merchantBalance->total_payout_balance, 2), "INR");
                $dashboardSummary['Bank Unclaimed Balance'] = numfmt_format_currency($fmt,round($unclaimed, 2), "INR");
            }
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

    public function getMMDashboardData($startDate, $endDate)
    {
        try {
            $startDate = Carbon::parse($startDate, "Asia/Kolkata")->format("Y-m-d");
            $endDate = Carbon::parse($endDate, "Asia/Kolkata")->format("Y-m-d");
            $fmt = numfmt_create( 'en_IN', NumberFormatter::DECIMAL);
            $merchantBalance = (new MerchantBalance())->getMerchantBalance($startDate, $endDate);
            if(isset($merchantBalance)) {
                foreach ($merchantBalance as $key => $merchant) {
                    $merchantBalance[$key]["closing_balance"] =numfmt_format_currency($fmt,  round((new MerchantBalance())->getMerchantLastClosingBalance($merchant->merchant_id), 2), "INR");
                    $merchantBalance[$key]["un_settled_balance"] =numfmt_format_currency($fmt,  round((new MerchantBalance())->getMerchantUnSettledBalance($merchant->merchant_id), 2), "INR");
                    $merchantBalance[$key]["payin"] =numfmt_format_currency($fmt,  $merchantBalance[$key]["payin"], "INR");
                    $merchantBalance[$key]["payout"] =numfmt_format_currency($fmt, $merchantBalance[$key]["payout"],  "INR");
                    $merchantBalance[$key]["refund"] =numfmt_format_currency($fmt,  $merchantBalance[$key]["refund"], "INR");
                    $merchantBalance[$key]["min_ticket"] =numfmt_format_currency($fmt,  (new Transactions())->getMinTicketSize($merchant->merchant_id,$startDate, $endDate), "INR");
                    $merchantBalance[$key]["max_ticket"] =numfmt_format_currency($fmt,   (new Transactions())->getMaxTicketSize($merchant->merchant_id,$startDate, $endDate), "INR");

                }
                $result['status'] = true;
                $result['message'] = "Merchant Management Dashboard Data Retrieved";
                $result['data'] = $merchantBalance;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Management Dashboard Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {

            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPgSummary($startDate, $endDate)
    {
        try {
            $startDate = DigiPayUtil::TO_UTC($startDate);
            $endDate = DigiPayUtil::TO_UTC($endDate);

            $pgTransaction = (new Transactions())->getTransactionSummaryForPgSummary($startDate, $endDate);
            $pgPayout = (new Payout())->getPayoutSummaryForPgSummary($startDate, $endDate);

            $pgSummary = [
                "collection" => null,
                "withdrawal" => null,
            ];

            if(isset($pgTransaction)) {
                foreach ($pgTransaction as $_pgTransaction) {
                    $pgSummary['collection'][] = [
                        "total_manual_collection" => $_pgTransaction->total_manual_collection,
                        "total_auto_collection" => $_pgTransaction->total_auto_collection,
                        "pg_type" => $_pgTransaction->pg_type,
                        "pg_detail" => $this->getPgDetails($_pgTransaction->pg_name, $_pgTransaction->meta_id, "PAYIN"),
                    ];
                }
            }

            if(isset($pgPayout)) {
                foreach ($pgPayout as $_pgPayout) {
                    $pgSummary['withdrawal'][] = [
                        "total_manual_withdrawal" => $_pgPayout->total_manual_withdrawal,
                        "total_auto_withdrawal" => $_pgPayout->total_auto_withdrawal,
                        "pg_type" => $_pgPayout->pg_type,
                        "pg_detail" => $this->getPgDetails($_pgPayout->pg_name, $_pgPayout->meta_id, "PAYOUT"),
                    ];
                }
            }

            $result['status'] = true;
            $result['message'] = "PG Summary Data Retrieved";
            $result['data'] = $pgSummary;
            return response()->json($result)->setStatusCode(200);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getMMDashboardBalanceData($merchantId)
    {
        try {
            $merchantBalance = (new MerchantBalance())->getMerchantBalanceByMid($merchantId);
            if(isset($merchantBalance)) {
                $result['status'] = true;
                $result['message'] = "Merchant Management Dashboard Data Retrieved";
                $result['data'] = [
                    "Payout Balance" => (new MerchantBalance())->getMerchantLastClosingBalance($merchantBalance->merchant_id),
                    "Unsettled Balance" => $merchantBalance->un_settled_balance,
                ];
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Merchant Management Dashboard Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function getPgDetails($pgName, $metaId, $pgSystem) {
        try {
            $pgRouter = (new PgRouter())->getRouterByPg($pgName);
            if(isset($pgRouter)) {
                if(strcmp($pgSystem, "PAYIN") === 0) {
                    if(isset($pgRouter->payin_meta_router)) {
                        $pgMeta = (new $pgRouter->payin_meta_router)->getPayInMeta($metaId);
                        if(isset($pgMeta)) {
                            return [
                                "label" => strcmp($pgRouter->pg_type, PgType::MANUAL) === 0 ? $pgMeta->account_holder_name : $pgMeta->label,
                                "account_id" => strcmp($pgRouter->pg_type, PgType::MANUAL) === 0 ? $pgMeta->av_bank_id : $pgMeta->account_id,
                                "account_number" => strcmp($pgRouter->pg_type, PgType::MANUAL) === 0 ? $pgMeta->account_number : null,
                                "bank_name" => strcmp($pgRouter->pg_type, PgType::MANUAL) === 0 ? $pgMeta->bank_name : null,
                                "pg_name" => strcmp($pgRouter->pg_type, PgType::MANUAL) !== 0 ? $pgName : null,
                            ];
                        }
                    }
                }
                if(strcmp($pgSystem, "PAYOUT") === 0) {
                    if(isset($pgRouter->payout_meta_router)) {
                        $pgMeta = (new $pgRouter->payout_meta_router)->getMetaById($metaId);
                        if(isset($pgMeta)) {
                            return [
                                "label" => $pgMeta->label,
                                "account_id" => $pgMeta->account_id,
                                "account_number" => strcmp($pgRouter->pg_type, PgType::MANUAL) === 0 ? $pgMeta->debit_account : null,
                                "pg_name" => $pgName,
                            ];
                        }
                    }
                }
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function getPgManagementDashboardSummary($pgType, $pgName, $pgAccount, $startDate, $endDate)
    {
        try {
            if(strcmp(strtolower($pgType), "payin") === 0) {
                return $this->getPayInPGSummary($pgType, $pgName, $pgAccount, $startDate, $endDate);
            }
            if(strcmp(strtolower($pgType), "payout") === 0) {
                return $this->getPayoutPGSummary($pgType, $pgName, $pgAccount, $startDate, $endDate);
            }
            $error['status'] = false;
            $error['message'] = "Invalid Request";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function getPayInPGSummary($pgType, $pgName, $pgAccount, $startDate, $endDate) {
        try {
            $paymentTurnover = (new PaymentTurnover())->getSummary($pgName, $pgAccount, $startDate, $endDate);
            if(isset($paymentTurnover)) {
                $parsedData = [];
                foreach ($paymentTurnover as $paymentT) {
                    $paymentT->pgMeta = $this->getPgMetaDetails($pgType, $paymentT->pg_name, $paymentT->meta_merchant_id);
                    $parsedData[] = $paymentT;
                }

                $result['status'] = true;
                $result['message'] = "Data Retrieved";
                $result['data'] = $parsedData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function getPayoutPGSummary($pgType, $pgName, $pgAccount, $startDate, $endDate) {
        try {
            $payoutTurnover = (new PayoutTurnover())->getSummary($pgName, $pgAccount, $startDate, $endDate);
            if(isset($payoutTurnover)) {
                $parsedData = [];
                foreach ($payoutTurnover as $paymentT) {
                    $paymentT->pgMeta = $this->getPgMetaDetails($pgType, $paymentT->pg_name, $paymentT->meta_merchant_id);
                    $parsedData[] = $paymentT;
                }
                $result['status'] = true;
                $result['message'] = "Data Retrieved";
                $result['data'] = $parsedData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function getPgMetaDetails($pgType, $pgName, $metaMerchantId)
    {
        try {
            $pgRouter = (new PgRouter())->getRouterByPg($pgName);
            if(isset($pgRouter)) {
                $pgMeta = null;
                if(strcmp(strtolower($pgType), "payin") === 0) {
                    if(isset($pgRouter->payin_meta_router)) {
                        $pgMeta = (new $pgRouter->payin_meta_router)->getMetaByMerchantId($metaMerchantId);
                    }
                }
                if(strcmp(strtolower($pgType), "payout") === 0) {
                    if(isset($pgRouter->payout_meta_router)) {
                        $pgMeta = (new $pgRouter->payout_meta_router)->getMetaByMerchantId($metaMerchantId);
                    }
                }

                if(isset($pgMeta)) {
                    return [
                        "account_id" => isset($pgMeta->account_id) ? $pgMeta->account_id : (isset($pgMeta->av_bank_id) ? $pgMeta->av_bank_id : null),
                        "label" => isset($pgMeta->label) ? $pgMeta->label : (isset($pgMeta->account_holder_name) ? $pgMeta->account_holder_name : null),
                        "turn_over" => $pgMeta->turn_over,
                        "is_active" => $pgMeta->is_active,
                    ];
                }
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    public function GetMMTransactionSummary($StartDate,$endDate,$MerchantId)
    {
        $startDate = \Carbon\Carbon::parse($StartDate, "Asia/Kolkata")->format("Y-m-d 00:00:00");
        $endDate = \Illuminate\Support\Carbon::parse($endDate, "Asia/Kolkata")->format("Y-m-d 23:59:59");

        $startDate = DigiPayUtil::TO_UTC($startDate);
        $endDate = DigiPayUtil::TO_UTC($endDate);

        $dashboardSummary= array();
        try {
            $fmt = numfmt_create( 'en_IN', NumberFormatter::DECIMAL);
            $total_txn = (new Transactions())->getTransactionSummaryForStatusTotalSummary($startDate,$endDate,$MerchantId);
            $pgTransaction = (new Transactions())->getTransactionSummaryForStatusSummary($startDate,$endDate,$MerchantId);
            if(isset($pgTransaction)) {
                foreach ($pgTransaction as $txn)
                {
                    $dashboardSummary[$txn->payment_status]=array();
                    $dashboardSummary[$txn->payment_status]['txn_count']=$txn->txn_count;
                    //$dashboardSummary[$txn->payment_status]['total_amount']=  numfmt_format_currency($fmt, round( $txn->total_amount), "INR");
                    $dashboardSummary[$txn->payment_status]['total_amount']=  $txn->total_amount;
                    $dashboardSummary[$txn->payment_status]['txn_count_per']=round( ($txn->txn_count * 100 )/$total_txn,2);
                }
            }
            $blankTransaction = (new Transactions())->getTransactionSummaryForBlankDataSummary($startDate,$endDate,$MerchantId);
            if(isset($blankTransaction)) {
                foreach ($blankTransaction as $txn) {
                    $dashboardSummary[$txn->showing_data]=array();
                    $dashboardSummary[$txn->showing_data]['txn_count']=$txn->txn_count;
                    $dashboardSummary[$txn->showing_data]['total_amount']=  round( $txn->total_amount);
                    $dashboardSummary[$txn->showing_data]['txn_count_per']=round( ($txn->txn_count * 100 )/$total_txn,2);
                    if(isset($dashboardSummary['Initialized']))
                    {
                        $dashboardSummary['Initialized']['txn_count']= $dashboardSummary['Initialized']['txn_count']-$txn->txn_count;
                        $dashboardSummary['Initialized']['total_amount']=   round( $dashboardSummary['Initialized']['total_amount']- $txn->total_amount);
                        $dashboardSummary['Initialized']['txn_count_per']= round( $dashboardSummary['Initialized']['txn_count_per']-round( ($txn->txn_count * 100 )/$total_txn,2),2);
                    }
                }
            }
            if(count($dashboardSummary)>0)
            {
                foreach ($dashboardSummary as &$summery) {

                    $summery['total_amount']=  numfmt_format_currency($fmt, round( $summery['total_amount']), "INR");
                }
            }
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
    public function GetMMPayoutSummary()
    {

        $dashboardSummary= array();
        try {
            $startDate = \Illuminate\Support\Carbon::now("Asia/Kolkata")->format("Y-m-d 00:00:00");
            $endDate = Carbon::now("Asia/Kolkata")->format("Y-m-d 23:59:59");
            $startDate = \Illuminate\Support\Carbon::parse($startDate, "Asia/Kolkata")->setTimezone("UTC")->format("Y-m-d H:i:s");
            $endDate = Carbon::parse($endDate, "Asia/Kolkata")->setTimezone("UTC")->format("Y-m-d H:i:s");
            $total_txn = (new Payout())->getPayoutSummaryForStatusTotalSummary($startDate,$endDate);
            $pgTransaction = (new Payout())->getPayoutSummaryForStatusSummary($startDate,$endDate);
            $fmt = numfmt_create( 'en_IN', NumberFormatter::DECIMAL);
            if(isset($pgTransaction)) {
                foreach ($pgTransaction as $txn)
                {
                    $dashboardSummary[$txn->payout_status]=array();
                    $dashboardSummary[$txn->payout_status]['txn_count']=$txn->txn_count;
                    $dashboardSummary[$txn->payout_status]['total_amount']=  numfmt_format_currency($fmt, round( $txn->total_amount), "INR");
                    $dashboardSummary[$txn->payout_status]['txn_count_per']=round( ($txn->txn_count * 100 )/$total_txn,2);
                }
            }
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

    public function getPayoutCrData($filterData,  $limit, $pageNo) {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $payoutCrData = (new PayoutCrData())->getPayoutCrData($filterData, $limit, $pageNo);
            if(isset($payoutCrData)) {
                $payoutCRData = $payoutCrData->items();
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['current_page'] = $payoutCrData->currentPage();
                $result['last_page'] = $payoutCrData->lastPage();
                $result['is_last_page'] = !$payoutCrData->hasMorePages();
                $result['total_item'] = $payoutCrData->total();
                $result['current_item_count'] = $payoutCrData->count();
                $result['data'] = $payoutCRData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Cr Data  Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $error['status'] = false;
            $error['message'] = "Error while get Payout Cr Data";
            return response()->json($error)->setStatusCode(400);
        }
    }

}
