<?php

namespace App\Classes\BankFileCompiler\Payout;

use App\Classes\Util\PayoutUtils;
use App\Console\Commands\WebHookCompiler\IDFCMailWebhookCompiler;
use App\Models\Management\Payout;
use App\Models\Management\PayoutManualReconciliation;
use App\Models\PaymentManual\BankStatementFile;
use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use App\Models\PaymentManual\IdfcMailWebhook;
use App\Models\PaymentManual\PayoutBankStatementFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IDFCBankStatementParser
{
    public function statementParse($id, $fileName, $transactionArray, $accountNumber)
    {
        try {

            $totalAddedUtr = 0;
            $totalTransaction = count($transactionArray[0]);

            if($totalTransaction > 1){
                (new PayoutBankStatementFile())->addTotalCount($id, $totalTransaction);
            }

            foreach ($transactionArray[0] as $key => $txn) {
                (new PayoutBankStatementFile())->addProgressCount($id, $key + 1);
                if (isset($txn[2])) {

                    echo "\n" . $txn[2];

                    if(Str::contains($txn[2],"NEFT RETURN")) {
                        $marchedData = explode("/",$txn[2]);
                        if (count($marchedData) == 3) {
                            $utrNumber = $marchedData[1];
                        }
                    }

                    if(isset($utrNumber) && !empty($utrNumber)) {
                        echo  "\n Return Found " . $utrNumber;
                        $payoutId = (new IdfcMailWebhook())->getPayoutIdByUtrAndAcc($utrNumber, $accountNumber);
                        if(isset($payoutId) && !empty($payoutId)){
                            (new Payout())->setIDFCPayoutPgRefIdForReturn($payoutId, $utrNumber);
                            echo  "\n Return Utr updated " . $utrNumber;
                        }else{
                            echo  "\n Return Utr Not Found In Hook UTR : ". $utrNumber. " Account Number : ".$accountNumber ;
                        }

                        $payoutDetails = (new Payout())->checkUtrExists($utrNumber);
                        if (isset($payoutDetails) && !empty($payoutDetails)) {
                            $payoutId = $payoutDetails->payout_id;
                            $merchantId = $payoutDetails->merchant_id;
                            $manualPayBatchId = $payoutDetails->manual_pay_batch_id;
                            $payoutAmount = $payoutDetails->payout_amount;
                            $added = (new PayoutManualReconciliation())->addReconPayoutForRecon($payoutId, $merchantId, $manualPayBatchId, $fileName, $id, $payoutAmount);
                            if ($added) {
                                $totalAddedUtr = $totalAddedUtr + 1;
                                echo "\n " . $utrNumber . " - " . $payoutId . " Record Added Success \n";
                            } else {
                                echo "\n " . $utrNumber . " - " . $payoutId . " Skipped \n";
                            }
                        }else{
                            echo "\n " . $utrNumber . " - ". " Skipped \n";
                        }
                    }

                    $utrNumber = null;

                }
                usleep(10);
            }

            (new PayoutBankStatementFile())->totalAddedUtr($id, $totalAddedUtr);

            echo  "\n Getting IDFC Meta " . $accountNumber;

            $idfcMeta = (new IDFCPayoutMeta())->getBankDetailsByNum($accountNumber);
            if(isset($idfcMeta) && !empty($idfcMeta)){
                if(isset($idfcMeta->account_id) && !empty($idfcMeta->account_id)){
                    $safePayout = (new Payout())->getSafePayoutList($idfcMeta->account_id);
                    if(isset($safePayout) && !empty($safePayout)){
                        foreach ($safePayout as $_safePayout){
                            $bankRRN = (new IdfcMailWebhook())->getBankRRN($_safePayout->payout_id, $accountNumber);
                            if(isset($bankRRN) && !empty($bankRRN)){
                                echo  "\n Utr Found In Hook When Success BANK RRN : ". $bankRRN. " Account Number : ".$accountNumber ;
                                $isExists =  (new Payout())->checkTempUtr($bankRRN);
                                if(!$isExists){
                                    $result = (new Payout())->setIDFCPayoutPgRefId($_safePayout->payout_id, $bankRRN);
                                    if($result){
                                        if(isset($_safePayout->customer_id) && !empty($_safePayout->customer_id)){
                                            (new PayoutUtils())->setCustomerPayoutLevelData($_safePayout,'BANK_PARSER');
                                        }
                                        echo "\n Temp Utr updated : ". "Payout Id :". $_safePayout->payout_id ." reference number :" .$bankRRN;
                                    }
                                }else{
                                    echo "\n Temp Utr All ready Exists : ", $isExists." reference number :" .$bankRRN;
                                }

                            }else{
                                echo  "\n Utr Not Found In Hook When Success BANK RRN : ". $bankRRN. " Account Number : ".$accountNumber ;
                            }
                        }
                    }else{
                        echo  "\n Safe Payout Not Found" . $accountNumber;
                    }
                }
            }else{
                echo  "\n IDFC Meta Not Found" . $accountNumber;
            }

        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            (new PayoutBankStatementFile())->markAsError($id);
            (new PayoutBankStatementFile())->setRemark($id, $ex->getMessage());
        }
    }
}
