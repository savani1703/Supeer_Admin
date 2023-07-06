<?php

namespace App\Classes\Util;


use App\Classes\TransactionUtrAndAmountChecker;
use App\Constant\RefundStatus;
use App\Models\Management\BankTransactions;
use App\Models\Management\BlockInfo;
use App\Models\Management\CustomerLevel;
use App\Models\Management\PgRouter;
use App\Models\Management\Refund;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\LateSuccess;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TransactionUtils
{

    public function getTransactions($filterData, $pgType, $limit, $pageNo) {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $transactions = (new Transactions())->getTransactions($filterData, $pgType, $limit, $pageNo);
            if(isset($transactions)) {
                $transactionsData = $this->parseWithPgLable($transactions->items());
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['current_page'] = $transactions->currentPage();
                $result['last_page'] = $transactions->lastPage();
                $result['is_last_page'] = !$transactions->hasMorePages();
                $result['total_item'] = $transactions->total();
                $result['current_item_count'] = $transactions->count();
                $result['data'] = $transactionsData;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Transaction  Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $error['status'] = false;
            $error['message'] = "Error while get transactions";
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function getTransactionSummary($filterData, $pgType){
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $transactionsSummary = (new Transactions())->getTransactionSummary($filterData, $pgType);
            if(isset($transactionsSummary)) {
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['summary'] = $transactionsSummary;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Transaction  Not found";
            $error['summary'] = [
                "total_txn" => 0,
                "total_payment_amount" => 0,
                "total_pg_fees" => 0,
                "total_associate_fees" => 0,
                "total_payable_amount" => 0,
            ];
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $error['status'] = false;
            $error['message'] = "Error while get transactions";
            $error['summary'] = [
                "total_txn" => 0,
                "total_payment_amount" => 0,
                "total_pg_fees" => 0,
                "total_associate_fees" => 0,
                "total_payable_amount" => 0,
            ];
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function getTransactionById($transactionId) {
        try {
            $transaction = (new Transactions())->getTransactionById($transactionId, self::SelectById());
            if(isset($transaction)) {
                $result['status'] = true;
                $result['message'] = 'Transaction  Details (ById) Retrieve successfully';
                $result['data'] = $transaction;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Transaction  Details (ById) Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Transaction  Details (ById) ";
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

    public function getTransactionByBrowserId($browserId) {
        try {
            $transaction = (new Transactions())->getTransactionByBrowserId($browserId);
            foreach ($transaction as $key =>  $_transaction){
                if(isset($_transaction->customer_id) && !empty($_transaction->customer_id)){
                    $lastSuccessDate  = (new Transactions())->getTransactionLastSuccessDateById($_transaction->customer_id);
                    if(isset($lastSuccessDate->success_at_ist) && !empty($lastSuccessDate->success_at_ist)){
                        $transaction[$key]['last_success_date'] = $lastSuccessDate->success_at_ist;
                    }
                }
            }
            if(isset($transaction)) {
                $result['status'] = true;
                $result['message'] = 'Transaction  Details (ByBrowserId) Retrieve successfully';
                $result['data'] = $transaction;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Transaction  Details (ByBrowserId) Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Transaction  Details (ByBrowserId) ";
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

    public function resendTransactionWebhook($transactionId) {
        try {
            if((new Transactions())->resendTransactionWebhook($transactionId)) {
                SupportUtils::logs("TRANSACTION", "Transaction Webhook Resend, Transaction_ID: $transactionId");
                $result['status'] = true;
                $result['message'] = 'Data Updated successfully';
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while update Data";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while update Data";
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

    public function updateTransactionTempUtr($transactionId, $tempUtr) {
        try {
           $bank_rec= (new BankTransactions())->isEligibleForTransactionTempUTRUpdate($tempUtr);
            if(isset($bank_rec)) {
                if($bank_rec->isget==0) {
                    if ((new Transactions())->updateTransactionTempUtr($transactionId, $tempUtr)) {
                        SupportUtils::logs("TRANSACTION", "Transaction Temp UTR Updated, Transaction_ID: $transactionId, TEMP_UTR: $tempUtr");
                        $result['status'] = true;
                        $result['message'] = "Temp UTR Updated : $transactionId";
                        return response()->json($result)->setStatusCode(200);
                    }
                }else
                {
                    $error['status'] = false;
                    $error['message'] = "UTR Already Used";
                    return response()->json($error)->setStatusCode(400);
                }
            }else
            {
                (new Transactions())->updateTransactionTempUtr($transactionId, $tempUtr);
                SupportUtils::logs("TRANSACTION", "Transaction Temp UTR Updated, Transaction_ID: $transactionId, TEMP_UTR: $tempUtr");
                $result['status'] = true;
                $result['message'] = "Temp UTR Updated : $transactionId";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while update UTR";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
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

    public function blockCustomerDetails($transactionId)
    {
        try {
            $transaction = (new Transactions())->getTransactionById($transactionId);
            if(isset($transaction)) {
                $blockData = [];
                if(isset($transaction->customer_email)) {
                    $blockData[] = ["block_data" => $transaction->customer_email,'column_name' => 'customer_email','merchant_id' => $transaction->merchant_id];
                }
                if(isset($transaction->customer_mobile)) {
                    $blockData[] = ["block_data" => $transaction->customer_mobile,'column_name' => 'customer_mobile','merchant_id' => $transaction->merchant_id];
                }
                if(isset($transaction->customer_id) && !empty($transaction->customer_id)) {
                    $blockData[] = ["block_data" => $transaction->customer_id,'column_name' => 'customer_id','merchant_id' => $transaction->merchant_id];
                    $allHidId = (new Transactions())->getAllHid($transaction->customer_id);
                    if(isset($allHidId) && !empty($allHidId)) {
                        foreach ($allHidId as $hidId) {
                            if (isset($hidId) && !empty($hidId) && strcmp(strtolower($hidId), 'na') !== 0) {
                                $blockData[] = ["block_data" => $hidId,'column_name' => 'browser_id','merchant_id' => $transaction->merchant_id];
                            }
                        }
                    }
                }
                if(isset($transaction->customer_ip) && strcmp($transaction->customer_ip,'35.154.141.44') !== 0 && strcmp($transaction->customer_ip,'51.77.97.196') !== 0) {
                    $blockData[] = ["block_data" => $transaction->customer_ip,'column_name' => 'customer_ip','merchant_id' => $transaction->merchant_id];
                }
                if(isset($transaction->payment_data) && !empty($transaction->payment_data)) {
                    $blockData[] = ["block_data" => $transaction->payment_data,'column_name' => 'payment_data','merchant_id' => $transaction->merchant_id];
                }
                if(isset($transaction->browser_id) && strcmp(strtolower($transaction->browser_id), 'na') !== 0) {
                    $blockData[] = ["block_data" => $transaction->browser_id,'column_name' => 'browser_id','merchant_id' => $transaction->merchant_id];
                }

                (new BlockInfo())->addBlockData($blockData);
                $result['status'] = true;
                $result['message'] = "Transaction Data Blocked";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Invalid Transaction";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
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

    public function blockCustomerAllDetails($browserId)
    {
        try {
            $transaction = (new Transactions())->getTransactionByBrowserIdForBlock($browserId);
            if(!isset($transaction) || empty($transaction)){
                $error['status'] = false;
                $error['message'] = "Block Details Not Found";
                return response()->json($error)->setStatusCode(400);
            }
            $blockData = [];
            foreach ($transaction as $_transaction){
                if(isset($_transaction->customer_id) && !empty($_transaction->customer_id)){
                    $blockData[] = ["block_data" => $_transaction->customer_id,'column_name' => 'customer_id','merchant_id' => $_transaction->merchant_id];
                    $customerEmailList = (new Transactions())->getAllCustomerEmailListById($_transaction->customer_id);
                    if(isset($customerEmailList) && !empty($customerEmailList)){
                        foreach ($customerEmailList as $_customerEmailList){
                            $blockData[] = ["block_data" => $_customerEmailList->customer_email,'column_name' => 'customer_email','merchant_id' => $_transaction->merchant_id];
                        }
                    }
                    $customerMobileList = (new Transactions())->getAllCustomerMobileListById($_transaction->customer_id);
                    if(isset($customerMobileList) && !empty($customerMobileList)){
                        foreach ($customerMobileList as $_customerMobileList){
                            $blockData[] = ["block_data" => $_customerMobileList->customer_mobile,'column_name' => 'customer_mobile','merchant_id' => $_transaction->merchant_id];
                        }
                    }
                    $allHidId = (new Transactions())->getAllHid($_transaction->customer_id);
                    if(isset($allHidId) && !empty($allHidId)) {
                        foreach ($allHidId as $hidId) {
                            if (isset($hidId) && !empty($hidId) && strcmp(strtolower($hidId), 'na') !== 0) {
                                $blockData[] = ["block_data" => $hidId,'column_name' => 'browser_id','merchant_id' => $_transaction->merchant_id];
                            }
                        }
                    }
                    $allPaymentData = (new Transactions())->getAllCustomerUpiListById($_transaction->customer_id);
                    if(isset($allPaymentData) && !empty($allPaymentData)){
                        foreach ($allPaymentData as $_allPaymentData){
                            $blockData[] = ["block_data" => $_allPaymentData->payment_data,'column_name' => 'payment_data','merchant_id' => $_transaction->merchant_id];
                        }
                    }
                }
            }

            if(isset($blockData) && !empty($blockData) && count($blockData) > 1){
                (new BlockInfo())->addBlockData($blockData);
                $result['status'] = true;
                $result['message'] = "Transaction Data Blocked";
                return response()->json($result)->setStatusCode(200);
            }

            $error['status'] = false;
            $error['message'] = "Block Failed";
            return response()->json($error)->setStatusCode(400);

        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
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

    public function transactionRefund($transactionId, $refundAmount, $remark)
    {
        try {
            $transactionAmount = (new Transactions())->getTransactionAmountForRefund($transactionId);
            $refundedAmount = (new Refund())->getRefundAmount($transactionId);

            if (($refundedAmount + $refundAmount) > $transactionAmount) {
                return response()->json(['status' => false,'message' => 'refund is not possible because refund amount is higher than transaction amount'])->setStatusCode(400);
            }

            $refundType = RefundStatus::PARTIAL_REFUND;
            if (($refundedAmount + $refundAmount) == $transactionAmount) {
                $refundType = RefundStatus::FULL_REFUND;
            }

            $refundId       = "refund_".DigiPayUtil::generateRandomString();
            $transaction    = (new Transactions())->getTransactionDetailsForRefund($transactionId);
            $refundResult   = (new Refund())->addRefundDetails($refundId, $transactionId, $transaction->merchant_id, $refundAmount, $transaction->currency, $refundType, $transaction->pg_name, $transaction->meta_id, $remark);
            if($refundResult){
                SupportUtils::logs('Give Refund',"User Give Refund (Transaction Id is '$transactionId )");
                $result['status'] = true;
                $result['message'] = 'User Refund success';
                return response()->json($result)->setStatusCode(200);
            }
            $result['status'] = false;
            $result['message'] = 'User Refund Failed';
            return response()->json($result)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while giveRefund";
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

    public static function SelectById() {
        return [
            'transaction_id',
            'merchant_order_id',
            'merchant_id',
            'customer_id',
            'customer_name',
            'customer_email',
            'payment_method',
            'customer_mobile',
            'customer_id',
            'currency',
            'payment_amount',
            'pg_fees',
            'associate_fees',
            'payable_amount',
            'is_settled',
            'payment_status',
            'payment_data',
            'pg_order_id',
            'pg_res_code',
            'pg_ref_id',
            'pg_res_msg',
            'bank_rrn',
            'pg_name',
            'is_webhook_call',
            'callback_url',
            'pg_method_id',
            'meta_id',
            'temp_selected_method_name',
            'meta_merchant_id',
            'meta_merchant_pay_id',
            'udf1',
            'udf2',
            'udf3',
            'udf4',
            'udf5',
            'customer_ip',
            'is_blocked',
            'showing_data',
            'reason',
            'is_vpn_ip',
            'cust_state',
            'cust_city',
            'cust_country',
            'ip_data',
            'created_at',
            'updated_at',
            'browser_id',
            'player_register_date',
            'player_deposit_amount',
            'player_deposit_count',
            'pg_type',
            'temp_bank_utr',
            'isget',
            'updated_at',
            'created_at',
            'success_at',
        ];
    }

    public static function CommanFilter($transactions, $filterData, $pgTye) {
        try {
            if(strcmp(strtolower($pgTye), "all") !== 0) {
                $transactions->where('pg_type', $pgTye);
            }

            if(isset($filterData) && sizeof($filterData) > 0) {
                if(isset($filterData['searchdata']) && !empty($filterData['searchdata'])  && strlen($filterData['searchdata']) > 3) {
                    $transactions->where('transaction_id','like', "%".$filterData['searchdata']);
                    $transactions->orWhere('merchant_order_id','like', "%".$filterData['searchdata']);
                    $transactions->orWhere('bank_rrn','like', "%".$filterData['searchdata']);
                    $transactions->orWhere('temp_bank_utr','like', "%".$filterData['searchdata']);
                    $transactions->orWhere('pg_order_id','like', "%".$filterData['searchdata']);
                    $transactions->orWhere('pg_ref_id','like', "%".$filterData['searchdata']);
                    $transactions->orWhere('customer_name','like', "%".$filterData['searchdata']);
                }
                if(isset($filterData['transaction_id']) && !empty($filterData['transaction_id'])) {
                    $transactions->where('transaction_id', $filterData['transaction_id']);
                }
                if(isset($filterData['customer_name']) && !empty($filterData['customer_name'])) {
                    $transactions->Where('customer_name','like', "%".$filterData['customer_name']);
                }
                if(isset($filterData['customer_id']) && !empty($filterData['customer_id'])) {
                    $transactions->where('customer_id', $filterData['customer_id']);
                }
                if(isset($filterData['merchant_order_id']) && !empty($filterData['merchant_order_id'])) {
                    $transactions->where('merchant_order_id', $filterData['merchant_order_id']);
                }
                if(isset($filterData['pg_ref_id']) && !empty($filterData['pg_ref_id'])) {
                    $transactions->where('pg_ref_id', $filterData['pg_ref_id']);
                }
                if(isset($filterData['payment_amount']) && !empty($filterData['payment_amount'])) {
                    $transactions->where('payment_amount', $filterData['payment_amount']);
                }
                if(isset($filterData['pg_order_id']) && !empty($filterData['pg_order_id'])) {
                    $transactions->where('pg_order_id', $filterData['pg_order_id']);
                }
                if(isset($filterData['merchant_id']) && !empty($filterData['merchant_id']) && strcmp(strtolower($filterData['merchant_id']), "all") !== 0) {
                    $transactions->where('merchant_id', $filterData['merchant_id']);
                }
                if(isset($filterData['customer_email']) && !empty($filterData['customer_email'])) {
                    $transactions->where('customer_email', $filterData['customer_email']);
                }
                if(isset($filterData['customer_mobile']) && !empty($filterData['customer_mobile'])) {
                    $transactions->where('customer_mobile', $filterData['customer_mobile']);
                }
                if(isset($filterData['meta_merchant_id']) && !empty($filterData['meta_merchant_id'])) {
                    $transactions->where('meta_merchant_id', $filterData['meta_merchant_id']);
                }
                if(isset($filterData['meta_id']) && !empty($filterData['meta_id']) && strcmp(strtolower($filterData['meta_id']), "all") !== 0) {
                    $transactions->where('meta_id', $filterData['meta_id']);
                }
                if(isset($filterData['udf1']) && !empty($filterData['udf1'])) {
                    $transactions->where('udf1', $filterData['udf1']);
                }
                if(isset($filterData['udf2']) && !empty($filterData['udf2'])) {
                    $transactions->where('udf2', $filterData['udf2']);
                }
                if(isset($filterData['udf3']) && !empty($filterData['udf3'])) {
                    $transactions->where('udf3', $filterData['udf3']);
                }
                if(isset($filterData['udf4']) && !empty($filterData['udf4'])) {
                    $transactions->where('udf4', $filterData['udf4']);
                }
                if(isset($filterData['udf5']) && !empty($filterData['udf5'])) {
                    $transactions->where('udf5', $filterData['udf5']);
                }
                if(isset($filterData['browser_id']) && !empty($filterData['browser_id'])) {
                    $transactions->where('browser_id', $filterData['browser_id']);
                }
                if(isset($filterData['cust_state']) && !empty($filterData['cust_state'])) {
                    if(strcmp($filterData['cust_state'], "ALL") !== 0) {
                        $transactions->where('cust_state', $filterData['cust_state']);
                    }
                }
                if(isset($filterData['cust_city']) && !empty($filterData['cust_city'])) {
                    $transactions->where('cust_city', $filterData['cust_city']);
                }
                if(isset($filterData['status']) && !empty($filterData['status']) && strcmp(strtolower($filterData['status']), "all") !== 0) {
                    $transactions->where('payment_status', $filterData['status']);
                }
                if(isset($filterData['pg_name']) && !empty($filterData['pg_name']) && strcmp(strtolower($filterData['pg_name']), "all") !== 0) {
                    $transactions->where('pg_name', $filterData['pg_name']);
                }
                if(isset($filterData['payment_method']) && !empty($filterData['payment_method']) && strcmp(strtolower($filterData['payment_method']), "all") !== 0) {
                    $transactions->where('payment_method', $filterData['payment_method']);
                }
                if(isset($filterData['bank_rrn']) && !empty($filterData['bank_rrn'])) {
                    $transactions->where('bank_rrn', $filterData['bank_rrn']);
                }
                if(isset($filterData['temp_bank_utr']) && !empty($filterData['temp_bank_utr'])) {
                    $transactions->where('temp_bank_utr', $filterData['temp_bank_utr']);
                }
                if(isset($filterData['onlytemputr']) && !empty($filterData['onlytemputr'])) {
                    $transactions->whereNotNull('temp_bank_utr');
                }
                if(isset($filterData['customer_ip']) && !empty($filterData['customer_ip'])) {
                    $transactions->where('customer_ip', $filterData['customer_ip']);
                }
                if(isset($filterData['payment_data']) && !empty($filterData['payment_data'])) {
                    $transactions->where('payment_data', $filterData['payment_data']);
                }
                if(isset($filterData['pg_type']) && !empty($filterData['pg_type']) && strcmp(strtolower($filterData['pg_type']), "all") !== 0) {
                    $transactions->where('pg_type', $filterData['pg_type']);
                }
                if(isset($filterData['showpage']) && !empty($filterData['showpage']) && strcmp(strtolower($filterData['showpage']), "all") !== 0) {
                    if(strcmp($filterData['showpage'],'odd_amount') === 0){
                        $transactions->whereRaw('(payment_amount % 2) != 0');
                    }else{
                        $transactions->where('showing_data', $filterData['showpage']);
                    }
                }
                if(isset($filterData['blockedUser']) && !empty($filterData['blockedUser']) && strcmp(strtolower($filterData['blockedUser']), "all") !== 0) {
                    if ($filterData['blockedUser']==="yes"){$transactions->whereNotNull('showing_data');} if ($filterData['blockedUser']==="no"){$transactions->whereNull('showing_data');}
                }
                if(isset($filterData['start_date']) && !empty($filterData['start_date']) && isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                    $transactions->whereBetween('created_at', [$filterData['start_date'], $filterData['end_date']]);
                }
                if(isset($filterData['player_start_date']) && !empty($filterData['player_start_date']) && isset($filterData['player_end_date']) && !empty($filterData['player_end_date'])) {
                    $playerStartDate    = Carbon::parse($filterData['player_start_date'])->format('d-M-Y');
                    $playerEndDate      = Carbon::parse($filterData['player_end_date'])->format('d-M-Y');
                    $transactions->whereIn('player_register_date', [$playerStartDate, $playerEndDate]);
                    $transactions->distinct('customer_id');
                }
                /*if(isset($filterData['success_start_date']) && !empty($filterData['success_start_date']) && isset($filterData['success_end_date']) && !empty($filterData['success_end_date'])) {
                    $transactions->whereBetween('success_at', [$filterData['success_start_date'], $filterData['success_end_date']]);
                }*/
                if(isset($filterData['min_amount']) && !empty($filterData['min_amount'] && $filterData['min_amount'] > 0 ) && isset($filterData['max_amount']) && !empty($filterData['max_amount']) && $filterData['max_amount'] > 0 ) {
                    $transactions->where('payment_amount', '>=', $filterData['min_amount']);
                    $transactions->where('payment_amount', '<=', $filterData['max_amount']);
                }
                if(isset($filterData['lateSuccess']) && !empty($filterData['lateSuccess'])) {
                 $transactions->where('merchant_id','MID_3UOP4XZR4OO17D')->whereRaw('created_at < DATE_SUB(success_at, INTERVAL 1 DAY)');
                }
             /* if(isset($filterData['cust_level']) && !empty($filterData['cust_level']) && strcmp(strtolower($filterData['cust_level']), "All") !== 0) {
                     $transactions->leftJoin("tbl_customer_level", "tbl_customer_level.customer_id", "=", "tbl_transaction.customer_id")->where('tbl_customer_level.user_security_level',$filterData['cust_level']);
               }*/
            }
            return $transactions;
        } catch (\Exception $ex) {
            return $transactions;
        }
    }

    private function parseWithPgLable($transactions)
    {
        $pgRouterarray=array();
        $pgMetaarray=array();
        $txns=new Transactions();
        try {
            if(isset($transactions)) {
                foreach ($transactions as $key => $transaction) {

                    if(isset($transaction->temp_selected_method_name) && !empty($transaction->temp_selected_method_name)){
                        if(strcmp($transaction->temp_selected_method_name,'FASTUPI') === 0){
                            $customerLevel = (new CustomerLevel())->getCustomerLeveling($transaction->merchant_id, $transaction->customer_id);
                            if(isset($customerLevel)){
                                $transactions[$key]['user_security_level'] = $customerLevel->user_security_level;
                                $transactions[$key]['user_created_at'] = $customerLevel->created_at_ist;
                            }
                        }
                    }

                    if(strcmp($transaction->payment_status,PaymentStatus::SUCCESS) === 0){
                        if(isset($transaction->bank_rrn) && !empty($transaction->bank_rrn)){
                            $successUpi = (new BankTransactions())->getTransactionUpiId($transaction->bank_rrn);
                            $transactions[$key]['success_upi_id'] = $successUpi;
                        }
                    }

                    /*$transactions[$key]['block_data_count'] = Cache::remember('block_data_count'.$transaction->transaction_id, 80, function () use ($transaction) {
                        $this->checkIsBlockedUser($transaction);
                    });*/

                    $transactions[$key]['block_data_count'] = $this->checkIsBlockedUser($transaction);

                    $transactions[$key]['is_risky'] = Cache::remember('is_risky'.$transaction->customer_id.$transaction->merchant_id, 80, function () use ($txns,$transaction) {
                       return $txns->checkCustomerisRisky($transaction->customer_id,$transaction->merchant_id);
                    });

                    if(isset($transaction->browser_id) && !empty($transaction->browser_id) && strcmp($transaction->browser_id,'NA') !== 0){
                        $transactions[$key]['total_customer_id'] = Cache::remember('total_customer_id'.$transaction->customer_id.$transaction->browser_id.$transaction->merchant_id, 10, function () use ($txns,$transaction) {
                            return $txns->checkDeviceHasMultipleCustomer($transaction->browser_id);
                        });
                    }

                    if(isset($transaction->meta_id) && isset($transaction->pg_name)) {
                        $pgRouter=null;
                        if(array_key_exists($transaction->pg_name,$pgRouterarray))
                        {
                            $pgRouter = $pgRouterarray[$transaction->pg_name];
                        }else {
                            $pgRouter = (new PgRouter())->getRouterByPg($transaction->pg_name);
                            $pgRouterarray[$transaction->pg_name]=$pgRouter;
                        }
                        if(isset($pgRouter)) {
                            if(isset($pgRouter->payin_meta_router)) {
                                $pgMeta=null;
                                if(array_key_exists($transaction->meta_id,$pgMetaarray))
                                {
                                    $pgMeta = $pgMetaarray[$transaction->meta_id];
                                }else {
                                    $pgMeta = (new $pgRouter->payin_meta_router)->getMetaForTransactionById($transaction->meta_id);
                                    $pgMetaarray[$transaction->meta_id]=$pgMeta;
                                }
                                if(isset($pgMeta)) {
                                    $transactions[$key]['pg_label'] = $pgMeta->label;
                                    if(isset($pgMeta->account_number)) $transactions[$key]['account_number'] = $pgMeta->account_number;
                                    if(isset($pgMeta->upi_id)) $transactions[$key]['upi_id'] = $pgMeta->upi_id;
                                    if(isset($pgMeta->ifsc_code)) $transactions[$key]['ifsc_code'] = $pgMeta->ifsc_code;
                                    if(isset($pgMeta->bank_name)) $transactions[$key]['bank_name'] = $pgMeta->bank_name;
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {

        }
        return $transactions;
    }

    public function checkIsBlockedUser($transaction){
        try {

            $blockData = array();

            if(isset($transaction->customer_email) && !empty($transaction->customer_email)) {
                $blockData[] =  $transaction->customer_email;
            }

            if(isset($transaction->customer_mobile) && !empty($transaction->customer_mobile)) {
                $blockData[] =  $transaction->customer_mobile;
            }

            if(isset($transaction->customer_id) && !empty($transaction->customer_id)) {
                $blockData[] =  $transaction->customer_id;
            }

            if(isset($transaction->customer_ip) && !empty($transaction->customer_ip)) {
                $blockData[] =  $transaction->customer_ip;
            }

            if(isset($transaction->payment_data) && !empty($transaction->payment_data)) {
                $blockData[] =  $transaction->payment_data;
            }

            if(isset($transaction->browser_id) && strcmp(strtolower($transaction->browser_id), 'na') !== 0) {
                $blockData[] =  $transaction->browser_id;
            }

            $manualBlockCount  = (new BlockInfo())->getManuallyBlockedCount($blockData);
            $autoBlockCount  = (new BlockInfo())->getAutoBlockedCount($blockData);
            return ['manual_blocked_count' => $manualBlockCount, 'auto_blocked_count' => $autoBlockCount];
        }catch (\Exception $ex){
            return ['manual_blocked_count' => 0, 'auto_blocked_count' => 0];
        }
    }

    public function getTransactionByUTR($bank_utr)
    {
        try {
            $transaction = (new Transactions())->getTransactionByUTR($bank_utr, self::SelectById());
            if(isset($transaction)) {
                $result['status'] = true;
                $result['message'] = 'Transaction  Details (ById) Retrieve successfully';
                $result['data'] = $transaction;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Transaction  Details (ById) Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Transaction  Details (ById) ";
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

    public function transactionSetUTR($transaction_id, $payment_utr)
    {
        try {
            SupportUtils::logs('Transaction UTR Set',"Transaction ID : $transaction_id, Payment UTR : $payment_utr");
            if(!isset($transaction_id))
            {
                $error['status'] = false;
                $error['message'] = "Transaction Details Not found";
                return response()->json($error)->setStatusCode(400);
            }
            if(!isset($payment_utr))
            {
                $error['status'] = false;
                $error['message'] = "UTR Details Not found";
                return response()->json($error)->setStatusCode(400);
            }
          $res=  (new Transactions())->setTempBankUtrNumberForAutoSucess($transaction_id, $payment_utr);
            if(isset($res)) {
                (new LateSuccess())->addRecord($transaction_id, $payment_utr);
                (new TransactionUtrAndAmountChecker())->checkTransactionForSuccess($transaction_id, $payment_utr);
                $result['status'] = true;
                $result['message'] = 'UTR Set successfully';
                $result['data'] = null;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Transaction  Details Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Transaction  Details (ById) ";
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

    public function transactionDelete( $transaction_id,  $payment_amount)
    {
        try {
            SupportUtils::logs('Transaction Delete',"Transaction ID : $transaction_id, Payment amount : $payment_amount");
            if(!isset($transaction_id))
            {
                $error['status'] = false;
                $error['message'] = "Transaction Details Not found";
                return response()->json($error)->setStatusCode(400);
            }
            if(!isset($payment_amount))
            {
                $error['status'] = false;
                $error['message'] = "Payment";
                return response()->json($error)->setStatusCode(400);
            }
            $res=  (new Transactions())->DeleteManualTransaction($transaction_id, $payment_amount);
            if($res) {
                $result['status'] = true;
                $result['message'] = 'Delete successfully';
                $result['data'] = null;
                return response()->json($result)->setStatusCode(200);
            }else
            {
                $error['status'] = false;
                $error['message'] = "Transaction Details Not found";
                return response()->json($error)->setStatusCode(400);
            }
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Transaction  Details (ById) ";
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
    public function transactionRemoveFees( $transaction_id,  $payment_amount)
    {
        try {
            SupportUtils::logs('Transaction Remove Fees',"Transaction ID : $transaction_id, Payment amount : $payment_amount");
            if(!isset($transaction_id))
            {
                $error['status'] = false;
                $error['message'] = "Transaction Details Not found";
                return response()->json($error)->setStatusCode(400);
            }
            if(!isset($payment_amount))
            {
                $error['status'] = false;
                $error['message'] = "Payment";
                return response()->json($error)->setStatusCode(400);
            }
            $res=  (new Transactions())->transactionRemoveFees($transaction_id, $payment_amount);
            if($res) {
                $result['status'] = true;
                $result['message'] = 'Remove Fees successfully';
                $result['data'] = null;
                return response()->json($result)->setStatusCode(200);
            }else
            {
                $error['status'] = false;
                $error['message'] = "Transaction Details Not found";
                return response()->json($error)->setStatusCode(400);
            }
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Transaction  Details (ById) ";
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

    public function getTransactionChartByHours( $startedDate,$endedDate,$merchantId,$custLevel) {
        try {

            $startDate = \Carbon\Carbon::parse($startedDate, "Asia/Kolkata")->format("Y-m-d 00:00:00");
            $endDate = \Illuminate\Support\Carbon::parse($endedDate, "Asia/Kolkata")->format("Y-m-d 23:59:59");

            $startDate = DigiPayUtil::TO_UTC($startDate);
            $endDate = DigiPayUtil::TO_UTC($endDate);


            $txnData = (new Transactions())->getTransactionChartDataByHours( $startDate, $endDate,$merchantId,$custLevel);

            if(isset($txnData)) {
                $chartData = [];
                $chartSeries = ["00","01","02","03","04","05","06","07","08","09","10","11",
                    "12","13","14","15","16","17","18","19","20","21","22","23"];
                $txnData = $txnData->toArray();
                foreach ($chartSeries as $key => $_chartSeries) {
                    $chartData["category"][$key] = $_chartSeries;

                    $chartData["success"][$key] = $txnData[self::multiSearch($txnData, [
                        "chart_time" => $_chartSeries,
                        "payment_status" => PaymentStatus::SUCCESS,
                    ])]['chart_count'] ?? 0;

                    $chartData["failed"][$key] = $txnData[self::multiSearch($txnData, [
                        "chart_time" => $_chartSeries,
                        "payment_status" => PaymentStatus::FAILED,
                    ])]['chart_count'] ?? 0;

                    $chartData["initialized"][$key] = $txnData[self::multiSearch($txnData, [
                        "chart_time" => $_chartSeries,
                        "payment_status" => PaymentStatus::INITIALIZED,
                    ])]['chart_count'] ?? 0;

                    $chartData["pending"][$key] = $txnData[self::multiSearch($txnData, [
                        "chart_time" => $_chartSeries,
                        "payment_status" => PaymentStatus::PENDING,
                    ])]['chart_count'] ?? 0;

                    $chartData["processing"][$key] = $txnData[self::multiSearch($txnData, [
                        "chart_time" => $_chartSeries,
                        "payment_status" => PaymentStatus::PROCESSING,
                    ])]['chart_count'] ?? 0;

                }
                $chart = [];
                $chart['category'] = $chartData['category'];
                $chart['series'] = [
                    [
                        "name" => "Success",
                        "data" => $chartData["success"],
                        "fillColor" => 'green',
                    ],
                    [
                        "name" => "Failed",
                        "data" => $chartData["failed"],
                        "fillColor" => 'red',
                    ],
                    [
                        "name" => "Initialized",
                        "data" => $chartData["initialized"],
                        "fillColor" => 'yellow',
                    ],
                    [
                        "name" => "Pending",
                        "data" => $chartData["pending"],
                        "fillColor" => 'blue',
                    ],
                    [
                        "name" => "Processing",
                        "data" => $chartData["processing"],
                        "fillColor" => 'orange',
                    ],
                ];
                return response()->json([
                    "status" => true,
                    "message" => "Chart data retrieved by hours",
                    "data" => $chart
                ]);
            }
            return response()->json([
                "status" => true,
                "message" => "Chart Data Empty",
            ])->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json([
                "status" => true,
                "message" => "error while get Chart data"
            ])->setStatusCode(500);
        }
    }
    public static function multiSearch(array $array, array $pairs)
    {
        foreach ($array as $aKey => $aVal) {
            $coincidences = 0;
            foreach ($pairs as $pKey => $pVal) {
                if (array_key_exists($pKey, $aVal) && $aVal[$pKey] == $pVal) {
                    $coincidences++;
                }
            }
            if ($coincidences == count($pairs)) {
                return $aKey;
            }
        }

        return -1;
    }


}
