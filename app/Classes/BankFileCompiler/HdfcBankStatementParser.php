<?php

namespace App\Classes\BankFileCompiler;

use App\Classes\Util\BankTransactionData;
use App\Models\Management\BankTransactions;
use App\Models\PaymentManual\BankParseUtr;
use App\Models\PaymentManual\BankStatementFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HdfcBankStatementParser
{
    public function statementParse($id, $fileName, $transactionArray, $accountNumber, $bankName){
        try {

            $totalAddedUtr = 0;
            $totalTransaction = count($transactionArray[0]);
            if($totalTransaction > 1){
                (new BankStatementFile())->addTotalCount($id, $totalTransaction);
            }
            foreach ($transactionArray[0] as $key => $txn) {
                (new BankStatementFile())->addProgressCount($id, $key + 1);
                if (isset($txn[5])) {
                    echo "\n" . $txn[5];
                    if (is_numeric((float)$txn[5]) && empty($txn[4])) {
                        $amount = (float)str_replace(",", "", trim($txn[5]));
                        $utrNumber = trim($txn[2]);
                        if(isset($utrNumber) && !empty($utrNumber)) {
                            if (!Str::contains(strtolower($txn[1]), "bulkpe")) {
                                $bankTransactionData = new BankTransactionData();
                                $bankTransactionData->accountNumber = $accountNumber;
                                $bankTransactionData->paymentUtr    = $utrNumber;
                                $bankTransactionData->amount        = $amount;
                                $bankTransactionData->bankName      = "HDFC";
                                $bankTransactionData->paymentMode   = "UPI";
                                $bankTransactionData->udf3          = $utrNumber;
                                $bankTransactionData->description   = trim($txn[1]);
                                $bankTransactionData->udf4          = trim($txn[1]);
                                $bankTransactionData->udf5          = trim($txn[1]);
                                $bankTransactionData->uniqeHash     = sha1($bankTransactionData->paymentUtr.$bankTransactionData->amount);
                                if (isset($accountNumber) && !empty($accountNumber)) {
                                    $res = (new BankTransactions())->addBankTransaction($bankTransactionData);
                                    if ($res) {
                                        (new BankParseUtr())->addBankParseUtr($id, $fileName, $bankTransactionData->paymentUtr, $bankTransactionData->amount);
                                        $totalAddedUtr = $totalAddedUtr + 1;
                                        echo "\n " . $utrNumber . "Record Added Success \n";
                                    } else {
                                        echo "\n " . $utrNumber . " Skipped \n";
                                    }
                                }
                            }
                        }
                    }
                }
                usleep(10);
            }

            (new BankStatementFile())->totalAddedUtr($id, $totalAddedUtr);

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
