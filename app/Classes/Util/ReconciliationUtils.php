<?php

namespace App\Classes\Util;

use App\Classes\AmountMismatchedTxn;
use App\Constant\PaymentStatus;
use App\Models\Management\BankTransactions;
use App\Models\Management\Payout;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\CustomerUpiMapping;
use App\Models\PaymentManual\LateSuccess;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class ReconciliationUtils
{

    public function recon($id, $type)
    {
        try {

            if(strcmp(strtolower($type), "payin") === 0) {
                if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_RECONCILIATION)) {
                    return response()->json(['status' =>true ,'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
            }

            if(strcmp(strtolower($type), "payout") === 0) {
                if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_RECONCILIATION)) {
                    return response()->json(['status' =>true ,'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
            }

            $payload = null;
            $this->checkIsEligibleForRecon($id, $type, $payload);
            if(isset($payload)) {
                $reconResponse = $this->reconStatusRequestToCheckout($payload, $type);
                if(isset($reconResponse)) {
                    SupportUtils::logs("RECONCILIATION","Fetch Status, PAYIN/OUT ID: $id");
                    if ((strcmp($type, "PAYIN") === 0)){
                        $data = base64_encode(view('reconciliation.payin-reconciliation')->with('reconResponse', $reconResponse)->render());
                        return response()->json(['status' => true , 'message' => 'Data Retrieve Successfully', 'data' => $data])->setStatusCode(200);
                    }
                    if ((strcmp($type, "PAYOUT") === 0)){
                        $data = base64_encode(view('reconciliation.payout-reconciliation')->with('reconResponse', $reconResponse)->render());
                        return response()->json(['status' =>true ,'message' => 'Data Retrieve Successfully','data'=>$data])->setStatusCode(200);
                    }
                 }
            }
            throw new \Exception("Error while recon Payment status");
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function reconAction($id, $type, $action) {
        try {

            if(strcmp(strtolower($type), "payin") === 0) {
                if(!(new AccessControl())->hasAccessModule(AccessModule::TRANSACTION_RECONCILIATION)) {
                    return response()->json(['status' =>true ,'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
            }

            if(strcmp(strtolower($type), "payout") === 0) {
                if(!(new AccessControl())->hasAccessModule(AccessModule::PAYOUT_RECONCILIATION)) {
                    return response()->json(['status' =>true ,'message' => AccessModule::ACCESS_DENIED])->setStatusCode(400);
                }
            }

            $payload = null;
            $this->checkIsEligibleForRecon($id, $type, $payload, $action);
            if(isset($payload)) {
                $reconResponse = $this->reconActionRequestToCheckout($payload, $type);
                if(isset($reconResponse)) {
                    SupportUtils::logs("RECONCILIATION","Recon Accept, PAYIN/OUT ID: $id, ACTION: $action");
                    return $reconResponse;
                }
            }
            throw new \Exception("Error while recon Payment status");
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function emptyBulkPeBal($accountId) {
        try {
            $payload = ['account_id' => $accountId];
            if(isset($payload)) {
                $reconResponse = $this->emptyBulkPeBalRequestToCheckout($payload);
                if(isset($reconResponse)) {
                    return $reconResponse;
                }
            }
            throw new \Exception("Error while empty bank");
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }



    private function getRequestUrlForReconStatus($type) {
        if(strcmp($type, "PAYIN") === 0) return "https://checkout.paytech123.com/api/v1/fetch/transaction/bank/status";
        if(strcmp($type, "PAYOUT") === 0) return "https://checkout.paytech123.com/api/v1/fetch/payout/bank/status";
        return null;
    }

    private function getRequestUrlForReconAction($type) {
        if(strcmp($type, "PAYIN") === 0) return "https://checkout.paytech123.com/api/v1/recon/bank/transaction";
        if(strcmp($type, "PAYOUT") === 0) return "https://checkout.paytech123.com/api/v1/recon/bank/payout";
        return null;
    }

    private function reconStatusRequestToCheckout($payload, $type) {
        $requestUrl = $this->getRequestUrlForReconStatus($type);
        if(isset($requestUrl)) {
            $jwt = DigiPayUtil::createJwtToken($payload);
            $header = [
                "headers" => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $jwt
                ]
            ];

            $reconResponse = $this->sendApiRequest($requestUrl, $payload, $header);
            if(isset($reconResponse)) {
                return $reconResponse;
            }
        }
        return null;
    }

    private function reconActionRequestToCheckout($payload, $type) {
        $requestUrl = $this->getRequestUrlForReconAction($type);
        if(isset($requestUrl)) {
            $jwt = DigiPayUtil::createJwtToken($payload);
            $header = [
                "headers" => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $jwt
                ]
            ];

            $reconResponse = $this->sendApiRequest($requestUrl, $payload, $header);
            if(isset($reconResponse)) {
                return $reconResponse;
            }
        }
        return null;
    }

    private function emptyBulkPeBalRequestToCheckout($payload) {
        $requestUrl = 'https://checkout.paytech123.com/api/v1/empty/bank/bal/bulkpe';
        if(isset($requestUrl)) {
            $jwt = DigiPayUtil::createJwtToken($payload);
            $header = [
                "headers" => [
                    'Content-Type' => 'application/json',
                    'Authorization' => $jwt
                ]
            ];

            $reconResponse = $this->sendApiRequest($requestUrl, $payload, $header);
            if(isset($reconResponse)) {
                return $reconResponse;
            }
        }
        return null;
    }

    private function checkIsEligibleForRecon($id, $type, &$payload, $action = null) {
        try {
            if(strcmp($type, "PAYIN") === 0) {
                $transactionDetails = (new Transactions())->getTransactionForRecon($id);
                if(!isset($transactionDetails)) {
                    throw new \Exception("Invalid Transaction Id");
                }
                if(!isset($transactionDetails->txn_token) || empty($transactionDetails->txn_token)) {
                    throw new \Exception("Invalid Transaction Id");
                }
                $payload = ['transaction_id' => $transactionDetails->transaction_id, 'txn_token' => $transactionDetails->txn_token, 'merchant_id' => $transactionDetails->merchant_id];
                if(isset($action)) {
                    $payload['action'] = strtolower($action);
                }
            } elseif(strcmp($type, "PAYOUT") === 0) {
                $payoutDetails = (new Payout())->getPayoutForRecon($id);
                if(!isset($payoutDetails)) {
                    throw new \Exception("Invalid Payout Id");
                }
                $payload = ['payout_id' => $payoutDetails->payout_id, 'merchant_id' => $payoutDetails->merchant_id];
                if(isset($action)) {
                    $payload['action'] = $action;
                }
            } else {
                throw new \Exception("Invalid Transaction Id");
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    private function sendApiRequest($requestUrl, $payload, $header)
    {
        try {
            $client = new Client($header);
            $response = $client->post($requestUrl,
                ['json' => $payload]
            );

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody()->getContents(),false);
            }
            return null;
        }catch (RequestException $ex) {
            return json_decode($ex->getResponse()->getBody(true)->getContents(),false);
        } catch (\Exception $ex) {
            Log::critical('sendApiRequest Error',['transactionRecon' => $ex->getMessage()]);
            return null;
        }
    }

    public function UtrRecon(): \Illuminate\Http\JsonResponse
    {
        try {
            $count=0;
            $arr=array();
            $utrarr=array();
            $banktxns = (new BankTransactions())->getPIForAutoSuccess();
            if(isset($banktxns)) {
                foreach ($banktxns as $txn) {
                    if(isset($txn->upi_id)) {
                        $txn->upi_id = trim($txn->upi_id);
                        $txn->upi_id = substr($txn->upi_id, 0, strpos($txn->upi_id, "@"));
                        if (!empty($txn->upi_id) && strlen($txn->upi_id) > 4) {
                            $customerinfos = (new CustomerUpiMapping())->getCustomerIDFromUPILike('MID_3UOP4XZR4OO17D', $txn->upi_id);
                            if (isset($customerinfos)) {
                                if ($customerinfos->count() == 1) {
                                    foreach ($customerinfos as $customerinfo) {
                                        $txndata = (new Transactions())->getTransactionForUpiAutoSuccess("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->amount);
                                        if (isset($txndata)) {
                                            $record = new AmountMismatchedTxn();
                                            $record->transaction_id = $txndata->transaction_id;
                                            $record->merchant_order_id = $txndata->merchant_order_id;
                                            $record->customer_id = $txndata->customer_id;
                                            $record->order_amount = $txndata->payment_amount;
                                            $record->payment_amount = $txn->amount;
                                            $record->payment_utr = $txn->payment_utr;
                                            $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                            $record->transaction_date = $txndata->created_at;
                                            $record->bank_txn_date = $txn->created_at;
                                            $record->upi_id = $txn->upi_id;
                                            if(isset($txndata->temp_bank_utr)) {
                                                if (strcmp($txndata->temp_bank_utr, $record->payment_utr) != 0) {
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }
                                                }
                                            }else
                                            {
                                                if (!in_array($record->payment_utr, $utrarr)) {
                                                    $utrarr[] = $record->payment_utr;
                                                    $arr[] = $record;
                                                }
                                            }
                                        }
                                    }
                                } else {

                                    $customer_id_arr = array();
                                    $customerinfo_old = null;
                                    foreach ($customerinfos as $customerinfo) {
                                        $customer_id_arr[$customerinfo->customer_id] = "ok";
                                        $customerinfo_old = $customerinfo;
                                    }
                                    if (count($customer_id_arr) == 1) {
                                        if (isset($customerinfo_old)) {
                                            $customerinfo = $customerinfo_old;
                                            $txndata = (new Transactions())->getTransactionForUpiAutoSuccess("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->amount);
                                            if (isset($txndata)) {
                                               $record = new AmountMismatchedTxn();
                                                $record->transaction_id = $txndata->transaction_id;
                                                $record->merchant_order_id = $txndata->merchant_order_id;
                                                $record->customer_id = $txndata->customer_id;
                                                $record->order_amount = $txndata->payment_amount;
                                                $record->payment_amount = $txn->amount;
                                                $record->payment_utr = $txn->payment_utr;
                                                $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                $record->transaction_date = $txndata->created_at;
                                                $record->bank_txn_date = $txn->created_at;
                                                $record->upi_id = $txn->upi_id;
                                                if(isset($txndata->temp_bank_utr)) {
                                                    if (strcmp($txndata->temp_bank_utr, $record->payment_utr) != 0) {
                                                        if (!in_array($record->payment_utr, $utrarr)) {
                                                            $utrarr[] = $record->payment_utr;
                                                            $arr[] = $record;
                                                        }
                                                    }
                                                }else
                                                {
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $txndata = (new Transactions())->getTransactionByUpiMobileAndAmount("MID_3UOP4XZR4OO17D", $txn->upi_id, $txn->amount);
                                        if(isset($txndata)) {
                                            $record = new AmountMismatchedTxn();
                                            $record->transaction_id = $txndata->transaction_id;
                                            $record->merchant_order_id = $txndata->merchant_order_id;
                                            $record->customer_id = $txndata->customer_id;
                                            $record->order_amount = $txndata->payment_amount;
                                            $record->payment_amount = $txn->amount;
                                            $record->payment_utr = $txn->payment_utr;
                                            $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                            $record->transaction_date = $txndata->created_at;
                                            $record->bank_txn_date = $txn->created_at;
                                            $record->upi_id = $txn->upi_id;
                                            if(isset($txndata->temp_bank_utr)) {
                                                if (strcmp($txndata->temp_bank_utr, $record->payment_utr) != 0) {
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }
                                                }
                                            }else
                                            {
                                                if (!in_array($record->payment_utr, $utrarr)) {
                                                    $utrarr[] = $record->payment_utr;
                                                    $arr[] = $record;
                                                }
                                            }
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }
            $banktxns = (new BankTransactions())->getPIForAutoSuccessWithoutCrossCheck();
            if (isset($banktxns)) {
                foreach ($banktxns as $txn) {
                    $count++;
                    $temptxndata = (new Transactions())->getTransactionSearchWithTempUTR("MID_3UOP4XZR4OO17D", $txn->payment_utr);
                    if (isset($temptxndata)) {
                        $record = new AmountMismatchedTxn();
                        $record->transaction_id = $temptxndata->transaction_id;
                        $record->merchant_order_id = $temptxndata->merchant_order_id;
                        $record->customer_id = $temptxndata->customer_id;
                        $record->order_amount = $temptxndata->payment_amount;
                        $record->payment_amount = $txn->amount;
                        $record->payment_utr = $txn->payment_utr;
                        $record->payment_tmp_utr = $temptxndata->temp_bank_utr;
                        $record->transaction_date = $temptxndata->created_at;
                        $record->bank_txn_date = $txn->created_at;
                        $record->upi_id = $txn->upi_id;
                        if (!in_array($record->payment_utr, $utrarr)) {
                            $utrarr[] = $record->payment_utr;
                            $arr[] = $record;
                        }

                    } else {


                        if (isset($txn->upi_id)) {

                            $txn->upi_id = trim($txn->upi_id);
                            $txn->upi_id = substr($txn->upi_id, 0, strpos($txn->upi_id, "@"));
                            if (!empty($txn->upi_id)) {
                                $customerinfos = (new CustomerUpiMapping())->getCustomerIDFromUPILike('MID_3UOP4XZR4OO17D', $txn->upi_id);
                                if (isset($customerinfos)) {
                                    if ($customerinfos->count() == 1) {
                                        foreach ($customerinfos as $customerinfo) {
                                            $txndata = (new Transactions())->getTransactionByUpiAndDate("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->created_at);
                                            if (isset($txndata)) {
                                                // File::append("NeedData.txt","\n".$txndata->transaction_id ." ---- ".$txn->upi_id." ---- Payment Amount :".$txn->amount." ---- Order Amount : ".$txndata->payment_amount." ----- ".$txn->payment_utr." ---- ". $txndata->created_at);
                                                $record = new AmountMismatchedTxn();
                                                $record->transaction_id = $txndata->transaction_id;
                                                $record->merchant_order_id = $txndata->merchant_order_id;
                                                $record->customer_id = $txndata->customer_id;
                                                $record->order_amount = $txndata->payment_amount;
                                                $record->payment_amount = $txn->amount;
                                                $record->payment_utr = $txn->payment_utr;
                                                $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                $record->transaction_date = $txndata->created_at;
                                                $record->bank_txn_date = $txn->created_at;
                                                $record->upi_id = $txn->upi_id;
                                                if (!in_array($record->payment_utr, $utrarr)) {
                                                    $utrarr[] = $record->payment_utr;
                                                    $arr[] = $record;
                                                }

                                            } else {
                                                $txndata = (new Transactions())->getTransactionByUpiAndWithoutDate("MID_3UOP4XZR4OO17D", $customerinfo->customer_id);
                                                if (isset($txndata)) {
                                                    $record = new AmountMismatchedTxn();
                                                    $record->transaction_id = $txndata->transaction_id;
                                                    $record->merchant_order_id = $txndata->merchant_order_id;
                                                    $record->customer_id = $txndata->customer_id;
                                                    $record->order_amount = $txndata->payment_amount;
                                                    $record->payment_amount = $txn->amount;
                                                    $record->payment_utr = $txn->payment_utr;
                                                    $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                    $record->transaction_date = $txndata->created_at;
                                                    $record->bank_txn_date = $txn->created_at;
                                                    $record->upi_id = $txn->upi_id;
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }

                                                }
                                            }
                                        }
                                    } else {


                                        $customer_id_arr = array();
                                        $customerinfo_old = null;
                                        foreach ($customerinfos as $customerinfo) {
                                            $customer_id_arr[$customerinfo->customer_id] = "ok";
                                            $customerinfo_old = $customerinfo;
                                        }
                                        if (count($customer_id_arr) == 1) {
                                            if (isset($customerinfo_old)) {
                                                $customerinfo = $customerinfo_old;
                                                $txndata = (new Transactions())->getTransactionByUpiAndDate("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->created_at);
                                                if (isset($txndata)) {
                                                    // File::append("NeedData.txt","\n".$txndata->transaction_id ." ---- ".$txn->upi_id." ---- Payment Amount :".$txn->amount." ---- Order Amount : ".$txndata->payment_amount." ----- ".$txn->payment_utr." ---- ". $txndata->created_at);
                                                    $record = new AmountMismatchedTxn();
                                                    $record->transaction_id = $txndata->transaction_id;
                                                    $record->merchant_order_id = $txndata->merchant_order_id;
                                                    $record->customer_id = $txndata->customer_id;
                                                    $record->order_amount = $txndata->payment_amount;
                                                    $record->payment_amount = $txn->amount;
                                                    $record->payment_utr = $txn->payment_utr;
                                                    $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                    $record->transaction_date = $txndata->created_at;
                                                    $record->bank_txn_date = $txn->created_at;
                                                    $record->upi_id = $txn->upi_id;
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }

                                                }
                                            }
                                        }

                                    }

                                }


                            }
                        }
                    }
                }
            }
            if(count($arr)>0) {
                return response()->json(['status' => true, 'message' => 'Data Retrieve Successfully', 'data' => $arr])->setStatusCode(200);
            }
        } catch (\Exception $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);

        }
        $error['status'] = false;
        $error['message'] = "No Records";;
        return response()->json($error)->setStatusCode(400);
    }

    public function UtrReconReport()
    {
        $banktxns = (new LateSuccess())->getRecords();
        $utrarr=array();
        if(isset($banktxns)) {
            if ($banktxns->count() > 0) {
                foreach ($banktxns as $dt) {
                    $txndata = (new Transactions())->getTransactionById($dt->transaction_id);
                    $banktxn = (new BankTransactions())->getTransactionByBankUtr($dt->utr_number);
                    if (isset($txndata) && isset($banktxn)) {
                        $record = new AmountMismatchedTxn();
                        $record->transaction_id = $txndata->transaction_id;
                        $record->merchant_order_id = $txndata->merchant_order_id;
                        $record->customer_id = $txndata->customer_id;
                        $record->order_amount = $txndata->payment_amount;
                        $record->payment_amount = $banktxn->amount;
                        $record->payment_utr = $banktxn->payment_utr;
                        $record->payment_tmp_utr = $txndata->temp_bank_utr;
                        $record->transaction_date = $txndata->created_at;
                        $record->bank_txn_date = $banktxn->created_at;
                        if (!in_array($record->payment_utr, $utrarr)) {
                            $utrarr[] = $record->payment_utr;
                            $arr[] = $record;
                        }
                    }
                }
                return response()->json(['status' => true, 'message' => 'Data Retrieve Successfully', 'data' => $arr])->setStatusCode(200);
            }
        }
        $error['status'] = false;
        $error['message'] = "No Records";;
        return response()->json($error)->setStatusCode(400);
    }
    public function SetUtrRecon()
    {
        try {
            $count=0;
            $arr=array();
            $utrarr=array();
            $banktxns = (new BankTransactions())->getPIForAutoSuccess();
            if(isset($banktxns)) {
                foreach ($banktxns as $txn) {
                    if(isset($txn->upi_id)) {
                        $txn->upi_id = trim($txn->upi_id);
                        $txn->upi_id = substr($txn->upi_id, 0, strpos($txn->upi_id, "@"));
                        if (!empty($txn->upi_id) && strlen($txn->upi_id) > 4) {
                            $customerinfos = (new CustomerUpiMapping())->getCustomerIDFromUPILike('MID_3UOP4XZR4OO17D', $txn->upi_id);
                            if (isset($customerinfos)) {
                                if ($customerinfos->count() == 1) {
                                    foreach ($customerinfos as $customerinfo) {
                                        $txndata = (new Transactions())->getTransactionForUpiAutoSuccess("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->amount);
                                        if (isset($txndata)) {
                                            $record = new AmountMismatchedTxn();
                                            $record->transaction_id = $txndata->transaction_id;
                                            $record->merchant_order_id = $txndata->merchant_order_id;
                                            $record->customer_id = $txndata->customer_id;
                                            $record->order_amount = $txndata->payment_amount;
                                            $record->payment_amount = $txn->amount;
                                            $record->payment_utr = $txn->payment_utr;
                                            $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                            $record->transaction_date = $txndata->created_at;
                                            $record->bank_txn_date = $txn->created_at;
                                            if(isset($txndata->temp_bank_utr)) {
                                                if (strcmp($txndata->temp_bank_utr, $record->payment_utr) != 0) {
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }
                                                }
                                            }else
                                            {
                                                if (!in_array($record->payment_utr, $utrarr)) {
                                                    $utrarr[] = $record->payment_utr;
                                                    $arr[] = $record;
                                                }
                                            }
                                        }
                                    }
                                } else {

                                    $customer_id_arr = array();
                                    $customerinfo_old = null;
                                    foreach ($customerinfos as $customerinfo) {
                                        $customer_id_arr[$customerinfo->customer_id] = "ok";
                                        $customerinfo_old = $customerinfo;
                                    }
                                    if (count($customer_id_arr) == 1) {
                                        if (isset($customerinfo_old)) {
                                            $customerinfo = $customerinfo_old;
                                            $txndata = (new Transactions())->getTransactionForUpiAutoSuccess("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->amount);
                                            if (isset($txndata)) {
                                                $record = new AmountMismatchedTxn();
                                                $record->transaction_id = $txndata->transaction_id;
                                                $record->merchant_order_id = $txndata->merchant_order_id;
                                                $record->customer_id = $txndata->customer_id;
                                                $record->order_amount = $txndata->payment_amount;
                                                $record->payment_amount = $txn->amount;
                                                $record->payment_utr = $txn->payment_utr;
                                                $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                $record->transaction_date = $txndata->created_at;
                                                $record->bank_txn_date = $txn->created_at;
                                                if(isset($txndata->temp_bank_utr)) {
                                                    if (strcmp($txndata->temp_bank_utr, $record->payment_utr) != 0) {
                                                        if (!in_array($record->payment_utr, $utrarr)) {
                                                            $utrarr[] = $record->payment_utr;
                                                            $arr[] = $record;
                                                        }
                                                    }
                                                }else
                                                {
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    else
                                    {
                                        $txndata = (new Transactions())->getTransactionByUpiMobileAndAmount("MID_3UOP4XZR4OO17D", $txn->upi_id, $txn->amount);
                                        if(isset($txndata)) {
                                            $record = new AmountMismatchedTxn();
                                            $record->transaction_id = $txndata->transaction_id;
                                            $record->merchant_order_id = $txndata->merchant_order_id;
                                            $record->customer_id = $txndata->customer_id;
                                            $record->order_amount = $txndata->payment_amount;
                                            $record->payment_amount = $txn->amount;
                                            $record->payment_utr = $txn->payment_utr;
                                            $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                            $record->transaction_date = $txndata->created_at;
                                            $record->bank_txn_date = $txn->created_at;
                                            if(isset($txndata->temp_bank_utr)) {
                                                if (strcmp($txndata->temp_bank_utr, $record->payment_utr) != 0) {
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }
                                                }
                                            }else
                                            {
                                                if (!in_array($record->payment_utr, $utrarr)) {
                                                    $utrarr[] = $record->payment_utr;
                                                    $arr[] = $record;
                                                }
                                            }
                                        }

                                    }
                                }
                            }
                        }
                    }
                }
            }
            $banktxns = (new BankTransactions())->getPIForAutoSuccessWithoutCrossCheck();
            if (isset($banktxns)) {
                foreach ($banktxns as $txn) {
                    $count++;
                    $temptxndata = (new Transactions())->getTransactionSearchWithTempUTR("MID_3UOP4XZR4OO17D", $txn->payment_utr);
                    if (isset($temptxndata)) {
                        $record = new AmountMismatchedTxn();
                        $record->transaction_id = $temptxndata->transaction_id;
                        $record->merchant_order_id = $temptxndata->merchant_order_id;
                        $record->customer_id = $temptxndata->customer_id;
                        $record->order_amount = $temptxndata->payment_amount;
                        $record->payment_amount = $txn->amount;
                        $record->payment_utr = $txn->payment_utr;
                        $record->payment_tmp_utr = $temptxndata->temp_bank_utr;
                        $record->transaction_date = $temptxndata->created_at;
                        $record->bank_txn_date = $txn->created_at;
                        if (!in_array($record->payment_utr, $utrarr)) {
                            $utrarr[] = $record->payment_utr;
                            $arr[] = $record;
                        }

                    } else {


                        if (isset($txn->upi_id)) {

                            $txn->upi_id = trim($txn->upi_id);
                            $txn->upi_id = substr($txn->upi_id, 0, strpos($txn->upi_id, "@"));
                            if (!empty($txn->upi_id)) {
                                $customerinfos = (new CustomerUpiMapping())->getCustomerIDFromUPILike('MID_3UOP4XZR4OO17D', $txn->upi_id);
                                if (isset($customerinfos)) {
                                    if ($customerinfos->count() == 1) {
                                        foreach ($customerinfos as $customerinfo) {
                                            $txndata = (new Transactions())->getTransactionByUpiAndDate("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->created_at);
                                            if (isset($txndata)) {
                                                // File::append("NeedData.txt","\n".$txndata->transaction_id ." ---- ".$txn->upi_id." ---- Payment Amount :".$txn->amount." ---- Order Amount : ".$txndata->payment_amount." ----- ".$txn->payment_utr." ---- ". $txndata->created_at);
                                                $record = new AmountMismatchedTxn();
                                                $record->transaction_id = $txndata->transaction_id;
                                                $record->merchant_order_id = $txndata->merchant_order_id;
                                                $record->customer_id = $txndata->customer_id;
                                                $record->order_amount = $txndata->payment_amount;
                                                $record->payment_amount = $txn->amount;
                                                $record->payment_utr = $txn->payment_utr;
                                                $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                $record->transaction_date = $txndata->created_at;
                                                $record->bank_txn_date = $txn->created_at;
                                                if (!in_array($record->payment_utr, $utrarr)) {
                                                    $utrarr[] = $record->payment_utr;
                                                    $arr[] = $record;
                                                }

                                            } else {
                                                $txndata = (new Transactions())->getTransactionByUpiAndWithoutDate("MID_3UOP4XZR4OO17D", $customerinfo->customer_id);
                                                if (isset($txndata)) {
                                                    $record = new AmountMismatchedTxn();
                                                    $record->transaction_id = $txndata->transaction_id;
                                                    $record->merchant_order_id = $txndata->merchant_order_id;
                                                    $record->customer_id = $txndata->customer_id;
                                                    $record->order_amount = $txndata->payment_amount;
                                                    $record->payment_amount = $txn->amount;
                                                    $record->payment_utr = $txn->payment_utr;
                                                    $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                    $record->transaction_date = $txndata->created_at;
                                                    $record->bank_txn_date = $txn->created_at;
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }

                                                }
                                            }
                                        }
                                    } else {


                                        $customer_id_arr = array();
                                        $customerinfo_old = null;
                                        foreach ($customerinfos as $customerinfo) {
                                            $customer_id_arr[$customerinfo->customer_id] = "ok";
                                            $customerinfo_old = $customerinfo;
                                        }
                                        if (count($customer_id_arr) == 1) {
                                            if (isset($customerinfo_old)) {
                                                $customerinfo = $customerinfo_old;
                                                $txndata = (new Transactions())->getTransactionByUpiAndDate("MID_3UOP4XZR4OO17D", $customerinfo->customer_id, $txn->created_at);
                                                if (isset($txndata)) {
                                                    // File::append("NeedData.txt","\n".$txndata->transaction_id ." ---- ".$txn->upi_id." ---- Payment Amount :".$txn->amount." ---- Order Amount : ".$txndata->payment_amount." ----- ".$txn->payment_utr." ---- ". $txndata->created_at);
                                                    $record = new AmountMismatchedTxn();
                                                    $record->transaction_id = $txndata->transaction_id;
                                                    $record->merchant_order_id = $txndata->merchant_order_id;
                                                    $record->customer_id = $txndata->customer_id;
                                                    $record->order_amount = $txndata->payment_amount;
                                                    $record->payment_amount = $txn->amount;
                                                    $record->payment_utr = $txn->payment_utr;
                                                    $record->payment_tmp_utr = $txndata->temp_bank_utr;
                                                    $record->transaction_date = $txndata->created_at;
                                                    $record->bank_txn_date = $txn->created_at;
                                                    if (!in_array($record->payment_utr, $utrarr)) {
                                                        $utrarr[] = $record->payment_utr;
                                                        $arr[] = $record;
                                                    }

                                                }
                                            }
                                        }

                                    }

                                }


                            }
                        }
                    }
                }
            }
            if(count($arr)>0) {
                foreach ($arr as  $dt)
                {
                    try {
                        (new TransactionUtils())->transactionSetUTR($dt->transaction_id, $dt->payment_utr);
                        $txn= (new Transactions())->getTransactionById($dt->transaction_id);
                       if(isset($txn))
                       {
                          if(strcmp($txn->payment_status,PaymentStatus::SUCCESS)!=0)
                          {
                              (new BankTransactions())->MarkAsUsed($dt->payment_utr);
                          }
                       }

                    }catch (\Exception $ex)
                    {
                        Log::error('Error in Exception', [
                            'class' => __CLASS__,
                            'function' => __METHOD__,
                            'file' => $ex->getFile(),
                            'line_no' => $ex->getLine(),
                            'error_message' => $ex->getMessage(),
                        ]);
                    }
                }
                return response()->json(['status' => true, 'message' => 'Set Data Successfully', 'data' => null])->setStatusCode(200);
            }
        } catch (\Exception $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);

        }
        $error['status'] = false;
        $error['message'] = "No Records";;
        return response()->json($error)->setStatusCode(400);
    }


}
