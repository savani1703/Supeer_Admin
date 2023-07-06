<?php

namespace App\Console\Commands;

use App\Classes\Util\PgName;
use App\Constant\PayoutStatus;
use App\Imports\UsersImport;
use App\Models\Management\Payout;
use App\Models\PaymentManual\PayoutBankStatementFileRecon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReconAllBankFile extends Command
{

    protected $signature = 'ReconAllBankFile';
    protected $description = 'Command description';

    public function handle()
    {
        try {
            $transactionArray = Excel::toArray(new UsersImport, "D:/omac/reon/Impre. ICICI - 20th to 27th.xlsx");
            $this->icici($transactionArray);
        }catch (\Exception $ex){
            dd($ex->getMessage());
        }
    }

    public function idfc($transactionArray){
        $accountNumber  = null;
        $fileName       = "Impre. ICICI - 20th to 27th.xlsx";
        $bankName       = "IDFC";
        $this->accountNumberFinder($transactionArray, $accountNumber);
        $missMatched = null;
        foreach ($transactionArray[0] as $key => $txn) {
            if (isset($txn[2])) {

                echo "\n" . $txn[2] . "count : " . $key;
                $utrNumber = null;
                $amount = null;
                $date = null;
                if(Str::contains($txn[2],"NEFT")) {
                    $marchedData = explode("/",$txn[2]);
                    if($marchedData){
                        if($marchedData[0] === "NEFT"){
                            $utrNumber = $marchedData[1];
                            $amount = $txn[4];
                            $date = $txn[0];
                        }
                        if($marchedData[0] === "NEFT RETURN"){
                            $utrNumber = $marchedData[1];
                            (new PayoutBankStatementFileRecon())->deleteUtr($utrNumber);
                        }
                    }
                }
                if(isset($utrNumber) && !empty($utrNumber) && isset($amount) && !empty($amount)) {
                    echo "\n *********************" . $utrNumber . "*********************";
                    $result = (new PayoutBankStatementFileRecon())->addUtr($utrNumber, $accountNumber, $fileName, $bankName, $amount, $date);
                    if ($result) {
                        echo "\n amount : " . $amount . " Utr : " . $utrNumber;
                    }
                }
                /*if(isset($utrNumber) && !empty($utrNumber)) {
                    $payoutDetails = (new Payout())->checkTempUtrExists($utrNumber);
                    if (isset($payoutDetails) && !empty($payoutDetails)) {
                        $payoutId = $payoutDetails->payout_id;
                        $merchantId = $payoutDetails->merchant_id;
                        $manualPayBatchId = $payoutDetails->manual_pay_batch_id;
                        if(strcmp($payoutDetails->pg_name,PgName::ICICI) === 0){
                            dd($payoutId);
                        }
                    }else{
                        $payoutDetails = (new Payout())->checkUtrExists($utrNumber);
                        if(!$payoutDetails){
                            echo "\n *********************" . $utrNumber ."*********************";
                            $result = (new PayoutBankStatementFileRecon())->addUtr($utrNumber, $accountNumber, $fileName, $bankName, $amount);
                            if($result){
                                echo "\n amount : " . $amount ." Utr : ". $utrNumber ;
                            }
                        }
                    }
                }*/
            }
        }
    }

    public function icici($transactionArray){
        $accountNumber  = null;
        $fileName       = "";
        $bankName       = "ICICI";
        $this->accountNumberFinder($transactionArray, $accountNumber);
        foreach ($transactionArray[0] as $key => $txn) {
            if (isset($txn[5]) && isset($txn[6])) {
                echo "\n" . $txn[5] . "count : " . $key;

                $utrNumber = null;
                $amount = null;
                $date = null;

                if(strcmp($txn[6],'DR') === 0){
                    if(Str::contains($txn[5],"MMT/IMPS")) {
                        $marchedData = explode("/",$txn[5]);
                        if($marchedData){
                            $utrNumber = $marchedData[2];
                            $amount = $txn[7];
                            $date = $txn[2];
                        }
                    }
                    if(Str::contains($txn[5],"INF/INFT")) {
                        $marchedData = explode("/",$txn[5]);
                        if($marchedData){
                            $utrNumber = $marchedData[2];
                            $amount = $txn[7];
                            $date = $txn[2];
                        }
                    }
                }

                if(isset($utrNumber) && !empty($utrNumber) && isset($amount) && !empty($amount)) {
                    echo "\n *********************" . $utrNumber . "*********************";
                    $result = (new PayoutBankStatementFileRecon())->addUtr($utrNumber, $accountNumber, $fileName, $bankName, $amount, $date);
                    if ($result) {
                        echo "\n amount : " . $amount . " Utr : " . $utrNumber;
                    }
                }
                /*if(isset($utrNumber) && !empty($utrNumber)) {
                    $payoutDetails = (new Payout())->checkUtrExists($utrNumber);
                    if(!$payoutDetails){
                        echo "\n *********************" . $utrNumber ."*********************";
                        (new PayoutBankStatementFileRecon())->addUtr($utrNumber, $accountNumber, $fileName, $bankName);
                    }
                }*/
            }
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
                if (Str::contains($txn[0], "Transactions List")) {
                    $data = explode("-",$txn[0]);
                    $accountNumber = trim($data[3]);
                    return;
                }

            }
        }catch (\Exception $ex){
            $accountNumber = null;
        }
    }

    /*public function safex($transactionArray){
        $accountNumber  = "AGEN1016057370";
        $fileName       = "";
        $bankName       = "SAFEXPAY";
        foreach ($transactionArray[0] as $key => $txn) {
            if (isset($txn[1])) {
                echo "\n" . $txn[1] . "count : " . $key;
                $pgRef = $txn[1];
                if(!empty($pgRef)) {
                    $payoutDetails = (new Payout())->getPayoutByPgRef($pgRef);
                    if(!$payoutDetails){
                        echo "\n *********************" . $pgRef ."*********************";
                        (new PayoutBankStatementFileRecon())->addUtr($pgRef, $accountNumber, $fileName, $bankName);
                    }
                }
            }
        }
    }*/
}
