<?php

namespace App\Classes\Util;

use App\Models\Management\BankTransactions;
use App\Models\Management\MerchantDetails;
use App\Models\PaymentManual\AvailableBank;
use Illuminate\Support\Facades\Log;

class BankTransactionUtils
{
    public function getBankTransactions($filterData, $limit, $pageNo) {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $bankTransaction = (new BankTransactions())->getBankTransactions($filterData, $limit, $pageNo);
            if(isset($bankTransaction)) {
                $result = DigiPayUtil::withPaginate($bankTransaction);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Bank Transactions Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function MarkAsUsed($payment_utr)
    {
        try {
            $banktxn = (new BankTransactions())->MarkAsUsed($payment_utr);
            if($banktxn) {
                SupportUtils::logs('BANK',"UTR USED, UTR: $payment_utr");
                $result['status'] = true;
                $result['message'] = "$payment_utr: USED";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "UTR Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while UTR USED";
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

    public function mergeUtr( $utr_ref_1, $utr_ref_2)
    {
        try {
            $utr_ref_1=trim($utr_ref_1);
            $utr_ref_2=trim($utr_ref_2);
            $banktxn1 = (new BankTransactions())->getTransactionByDefBankUtr($utr_ref_1);
            if(!isset($banktxn1))
            {
                $error['status'] = false;
                $error['message'] = "Bank UTR 1 Not found";
                return response()->json($error)->setStatusCode(400);
            }
            $banktxn2 = (new BankTransactions())->getTransactionByDefBankUtr($utr_ref_2);
            if(!isset($banktxn2))
            {
                $error['status'] = false;
                $error['message'] = "Bank UTR 2 Not found";
                return response()->json($error)->setStatusCode(400);
            }
            if($banktxn1->isget==1) {

                $result['status'] = false;
                $result['message'] = "$utr_ref_1: is Already USED";
                return response()->json($result)->setStatusCode(400);
            }
            if($banktxn2->isget==1) {

                $result['status'] = false;
                $result['message'] = "$utr_ref_2: is Already USED";
                return response()->json($result)->setStatusCode(400);
            }
            SupportUtils::logs('BANK',"Merge UTR , ". $utr_ref_1." and ".$utr_ref_2." amount 1 = ".$banktxn1->amount." amount 2 = ".$banktxn2->amount);
            $res1 = (new BankTransactions())->MarkAsUsed($utr_ref_1);
            if($res1) {
                SupportUtils::logs('BANK',"UTR USED, UTR: $utr_ref_1 for Merge Txn ");
                $newamount=$banktxn1->amount+$banktxn2->amount;
                $res2= (new BankTransactions())->MergeUpdateAmount($utr_ref_2,$newamount);
                if($res2)
                {
                    $result['status'] = true;
                    $result['message'] ="Merge UTR 1 = ". $utr_ref_1." and UTR 2 = ".$utr_ref_2." amount 1 = ".$banktxn1->amount." amount 2 = ".$banktxn2->amount." Successfully";
                    return response()->json($result)->setStatusCode(200);
                }
            }

            $error['status'] = false;
            $error['message'] = "Failed";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while UTR USED";
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

    public function addBankTransaction($paymentUtr, $amount, $accountNumber)
    {
        try {
            $accountDetails = (new AvailableBank())->getAccountDetailsByAccountNumber($accountNumber);
            if(!isset($accountDetails) || empty($accountDetails)){
                $error['status'] = false;
                $error['message'] = "account details not found";
                return response()->json($error)->setStatusCode(400);
            }

            $emailId = DigiPayUtil::getAuthUser();

            $bankTransactionData = new BankTransactionData();
            $bankTransactionData->accountNumber = $accountNumber;
            $bankTransactionData->paymentUtr    = trim($paymentUtr);
            $bankTransactionData->amount        = $amount;
            $bankTransactionData->bankName      = $accountDetails->bank_name;
            $bankTransactionData->paymentMode   = "UPI";
            $bankTransactionData->udf3          = trim($paymentUtr);
            $bankTransactionData->description   = "manual Add";
            $bankTransactionData->udf4          = "manual Add";
            $bankTransactionData->udf5          = $emailId;
            $bankTransactionData->uniqeHash     = sha1($bankTransactionData->paymentUtr.$bankTransactionData->amount);

            $bankTranslation = (new BankTransactions())->addBankTransaction($bankTransactionData);
            if($bankTranslation){
                SupportUtils::logs('BANK_TRANSACTION',"Bank Transaction Added, UTR: $paymentUtr, AMOUNT: $amount, ACCOUNT NAME: $accountDetails->account_holder_name, ACCOUNT NUMBER: $accountDetails->account_number");
                $error['status'] = true;
                $error['message'] = "utr added successfully";
                return response()->json($error)->setStatusCode(200);
            }

            $error['status'] = true;
            $error['message'] = "utr add failed";
            return response()->json($error)->setStatusCode(400);

        }catch (\Exception $ex){
            $error['status'] = false;
            $error['message'] = "Error while UTR USED";
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
    public function getAvailableBank($bankName) {
        try {
            $data = (new AvailableBank())->getAvailableBank($bankName);
            if(isset($data)) {
                $result['status'] = true;
                $result['message'] = 'Bank Detail Retried SuccessFully';
                $result['data'] = $data;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Bank Detail Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateMobileNumber($pk, $value)
    {
        try {

            if ((new BankTransactions())->where('id',$pk)->update(
                ['mobile_number'=>$value]
            )) {
                SupportUtils::logs('BANKTXN', "BANKTXN, ID: $pk, MOBILE: $value");
                return response()->json([
                    "status" => true,
                    "message" => "Merchant Is PayIn Auto Fees Updated",
                ])->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Mobile Number";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }



    }
}
