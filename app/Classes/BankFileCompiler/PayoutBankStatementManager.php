<?php

namespace App\Classes\BankFileCompiler;

use App\Classes\BankFileCompiler\Payout\IDFCBankStatementParser;
use App\Classes\BankFileCompiler\Payout\YesBankStatementParser;
use App\Classes\Util\PgName;
use App\Classes\Util\Providers;
use App\Models\PaymentManual\AvailableBank;
use App\Models\PaymentManual\BankStatementFile;
use App\Models\PaymentManual\IDFC\IDFCPayoutMeta;
use App\Models\PaymentManual\PayoutBankStatementFile;
use App\Models\PaymentManual\YES\YesPayoutMeta;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PayoutBankStatementManager
{
    public function parseManager($id, $fileName, $transactionArray){

        try {

            $accountNumber  = null;
            $pgName  = null;
            $this->accountNumberFinder($transactionArray, $accountNumber, $pgName);
            if(strcmp($pgName,PgName::IDFC) === 0){
                echo "\n IDFC BANK DETECTED";
                $accountDetails = (new IDFCPayoutMeta())->getBankDetailsByNum($accountNumber);
            }
            if(strcmp($pgName,PgName::YES) === 0){
                echo "\n YES BANK DETECTED";
                $accountDetails = (new YesPayoutMeta())->getBankDetailsByNum($accountNumber);
            }

            if(!isset($accountDetails) || empty($accountDetails)){
                sleep(1);
                echo "sleep for 1 sec.. account not found";
            }

            $accountNumber = null;
            if(isset($accountDetails->debit_account) && !empty($accountDetails->debit_account)){
                $accountNumber = $accountDetails->debit_account;
            }

            if(empty($accountNumber)){
                (new PayoutBankStatementFile())->setRemark($id, 'ACCOUNT_NUMBER_NOT_FOUND');
                (new PayoutBankStatementFile())->markAsUsed($id);
                exit();
            }

            (new PayoutBankStatementFile())->setAccountNumber($id, $accountNumber);
            if(strcmp($pgName,PgName::IDFC) === 0){
                echo "\n Parser Moved in IDFC Bank";
                (new IDFCBankStatementParser())->statementParse($id, $fileName, $transactionArray, $accountNumber);
            }
            if(strcmp($pgName,PgName::YES) === 0){
                echo "\n Parser Moved in YES Bank";
                (new YesBankStatementParser())->statementParse($id, $fileName, $transactionArray, $accountNumber);
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
            (new PayoutBankStatementFile())->markAsError($id);
            (new PayoutBankStatementFile())->setRemark($id, $ex->getMessage());
        }

    }

    private function accountNumberFinder($transactionArray, &$accountNumber, &$pgName)
    {
        try {

            foreach ($transactionArray[0] as $txn) {
                if (Str::contains($txn[0], "ACCOUNT NUMBER")) {
                    $accountNumber = $txn[1];
                    $pgName = PgName::IDFC;
                    return;
                }
                if (Str::contains($txn[0], "Customer Id")) {
                    $mixAccountNumber = $txn[0];
                    $marchedData = explode(":",$mixAccountNumber);
                    $customerId = trim($marchedData[1]);
                    if(isset($customerId) && !empty($customerId)){
                        $accountNumber = (new YesPayoutMeta())->getAccountNumberByCustomerId($customerId);
                        $pgName = PgName::YES;
                        return;
                    }
                }
            }
        }catch (\Exception $ex){
            $accountNumber = null;
        }
    }
}
