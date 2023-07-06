<?php

namespace App\Classes\BankFileCompiler;


use App\Classes\Util\Providers;
use App\Models\PaymentManual\AvailableBank;
use App\Models\PaymentManual\BankStatementFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BankStatementManager
{
    public function parseManager($id, $fileName, $transactionArray){

        try {

            $accountNumber  = null;
            $this->accountNumberFinder($transactionArray, $accountNumber);
            $accountDetails = (new AvailableBank())->getBankDetailsByNum($accountNumber);

            if(!isset($accountDetails) || empty($accountDetails)){
                sleep(1);
                echo "sleep for 1 sec.. account not found";
            }

            $accountNumber = null;
            if(isset($accountDetails->account_number) && !empty($accountDetails->account_number)){
                $accountNumber = $accountDetails->account_number;
            }

            if(empty($accountNumber)){
                (new BankStatementFile())->setRemark($id, 'ACCOUNT_NUMBER_NOT_FOUND');
                (new BankStatementFile())->markAsUsed($id);
                exit();
            }

            $bankName = $accountDetails->bank_name;
            if(empty($bankName)){
                (new BankStatementFile())->setRemark($id, 'BANK_NAME_NOT_FOUND');
                (new BankStatementFile())->markAsUsed($id);
                exit();
            }
            (new BankStatementFile())->setAccountNumber($id, $accountNumber);
            $bankName = trim($bankName);
            if(strcmp($bankName, Providers::FEDRAL) === 0 || strcmp($bankName, "FEDERAL") === 0){
                echo "\n Bank Parse Init OF ".Providers::FEDRAL;
                (new FederalBankStatementParser())->statementParse($id, $fileName, $transactionArray, $accountNumber, $bankName);
            }elseif (strcmp($bankName, Providers::HDFC) === 0 || strcmp($bankName, "HDFC BANK") === 0){
                echo "\n Bank Parse Init OF ".Providers::HDFC;
                //(new HdfcBankStatementParser())->statementParse($id, $fileName, $transactionArray, $accountNumber, $bankName);
            }
        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            echo $ex->getMessage();
            (new BankStatementFile())->markAsError($id);
            (new BankStatementFile())->setRemark($id, $ex->getMessage());
        }

    }

    private function accountNumberFinder($transactionArray, &$accountNumber)
    {
        try {
            foreach ($transactionArray[0] as $txn) {
                if (Str::contains($txn[0], "Account No")) {
                    $accountNumber = $txn[2];
                    return;
                }

                if(isset($txn[4]) && !empty($txn[4])){
                    if (Str::contains($txn[4], "Account No :")) {
                        $explode = explode(" ",$txn[4]);
                        if(isset($explode) && !empty($explode) && count($explode) > 0){
                            $accountNumber = $explode[2] ? str_replace(':','',$explode[2]) : null;
                        }
                    }
                }

            }
        }catch (\Exception $ex){
            $accountNumber = null;
        }
    }
}
