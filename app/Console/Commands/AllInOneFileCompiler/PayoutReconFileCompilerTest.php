<?php

namespace App\Console\Commands\AllInOneFileCompiler;

use App\Classes\BankFileCompiler\PayoutBankStatementManager;
use App\Imports\UsersImport;
use App\Models\Management\Payout;
use App\Models\Management\PayoutManualReconciliation;
use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use App\Models\PaymentManual\IdfcMailWebhook;
use App\Models\PaymentManual\PayoutBankStatementFile;
use App\Models\PaymentManual\YES\YesPayoutMeta;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PayoutReconFileCompilerTest extends Command
{
    protected $signature = 'PayoutReconFileCompilerTestV1';
    protected $description = 'Command description';
    public function handle()
    {
        $id = null;
        try {

            $fileName       = "7293978811_Manasvi 04.06.2023.xlsx";
            ini_set('memory_limit', '-1');
            $accountNumber = null;
            $transactionArray = Excel::toArray(new UsersImport, $fileName, 's3-payout-recon');
            if (!isset($transactionArray) || empty($transactionArray)) {
                sleep(1);
                echo "sleep for 1 sec";
                exit();
            }
            $this->accountNumberFinder($transactionArray, $accountNumber);
            $this->statementParseForYes($transactionArray, $fileName);


        }catch (\Exception $ex){
            dd($ex->getMessage());
            echo $ex->getMessage();
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }

    public function statementParseForYes($transactionArray, $fileName){
        try {
            //dd($transactionArray);
            foreach ($transactionArray[0] as $key => $txn) {
                if(isset($txn[7]) && !empty($txn[7]) && isset($txn[8]) && !empty($txn[8]) && isset($txn[9]) && !empty($txn[9])){
                    $payoutId           = $txn[7];
                    $referenceNumber    = $txn[8];
                    $status             = $txn[9];
                    $checkPayout = (new Payout())->checkPayoutEligibleForYes($payoutId);
                    if($checkPayout){
                        if(strcmp($status,'Success') === 0 && strlen($referenceNumber) === 12){
                            (new Payout())->markPayoutAsSuccssForYes($payoutId, $referenceNumber);
                            echo "\n Mark As Success Payout Id Is : ".  $payoutId;
                        }elseif (strcmp($status,'Rejected/Failed at beneficiary bank') === 0){
                            (new Payout())->markPayoutAsFailedForYes($payoutId, $status);
                            echo "\n Mark As Failed Payout Id Is : ".  $payoutId;
                        }
                    }else{
                        echo "\n Payout Not Eligible";
                    }
                }
            }

        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }

    private function accountNumberFinder($transactionArray, &$accountNumber)
    {
        try {

            foreach ($transactionArray[0] as $txn) {
                if (Str::contains($txn[0], "ACCOUNT NUMBER")) {
                    $accountNumber = $txn[1];
                    return;
                }
                if (Str::contains($txn[0], "Customer Id")) {
                    $mixAccountNumber = $txn[0];
                    $marchedData = explode(":",$mixAccountNumber);
                    $customerId = trim($marchedData[1]);
                    if(isset($customerId) && !empty($customerId)){
                        $accountNumber = (new YesPayoutMeta())->getAccountNumberByCustomerId($customerId);
                    }
                    return;
                }
            }
        }catch (\Exception $ex){
            $accountNumber = null;
        }
    }

    public function statementParse($transactionArray, $fileName, $accountNumber)
    {
        try {

            $totalAddedUtr = 0;
            $totalTransaction = count($transactionArray[0]);

            foreach ($transactionArray[0] as $key => $txn) {
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
                            echo  "\n ********* Return Utr updated ********** " . $utrNumber;
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


            echo  "\n Getting IDFC Meta " . $accountNumber;

            $idfcMeta = (new IDFCPayoutMeta())->getBankDetailsByNum($accountNumber);
            if(isset($idfcMeta) && !empty($idfcMeta)){
                if(isset($idfcMeta->account_id) && !empty($idfcMeta->account_id)){
                    $safePayout = (new Payout())->getSafePayoutList($idfcMeta->account_id);
                    dd($safePayout->count());
                    /*if(isset($safePayout) && !empty($safePayout)){
                        $bankRRN = (new IdfcMailWebhook())->getBankRRN($safePayout->payout_id, $accountNumber);
                        if(isset($bankRRN) && !empty($bankRRN)){
                            echo  "\n Utr Found In Hook When Success BANK RRN : ". $bankRRN. " Account Number : ".$accountNumber ;

                            $isExists =  (new Payout())->checkTempUtr($bankRRN);
                            if(!$isExists){
                                $result = (new Payout())->setIDFCPayoutPgRefId($safePayout->payout_id, $bankRRN);
                                if($result){
                                    echo "\n Temp Utr updated : ". "Payout Id :". $safePayout->payout_id ." reference number :" .$bankRRN;
                                }
                            }else{
                                echo "\n Temp Utr All ready Exists : ", $isExists." reference number :" .$bankRRN;
                            }

                        }else{
                            echo  "\n Utr Not Found In Hook When Success BANK RRN : ". $bankRRN. " Account Number : ".$accountNumber ;
                        }
                    }else{
                        echo  "\n Safe Payout Not Found" . $accountNumber;
                    }*/
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
        }
    }
}
