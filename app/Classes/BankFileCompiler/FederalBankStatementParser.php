<?php

namespace App\Classes\BankFileCompiler;

use App\Classes\Util\BankTransactionData;
use App\Models\Management\BankTransactions;
use App\Models\PaymentManual\BankParseUtr;
use App\Models\PaymentManual\BankStatementFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FederalBankStatementParser
{

    public function statementParse($id, $fileName, $transactionArray, $accountNumber, $bankName)
    {
        try {

            $totalAddedUtr = 0;
            $totalTransaction = count($transactionArray[0]);

            if($totalTransaction > 1){
                (new BankStatementFile())->addTotalCount($id, $totalTransaction);
            }

            foreach ($transactionArray[0] as $key => $txn) {
                (new BankStatementFile())->addProgressCount($id, $key + 1);
                if (isset($txn[8])) {

                    echo "\n" . $txn[8];
                    if (is_numeric((float)$txn[8]) && is_numeric((float)$txn[9]) && empty($txn[7])) {

                        preg_match('/UPI IN\/(.+?)\//s', $txn[2], $marchedData);
                        if (count($marchedData) == 2) {
                            $utrNumber = $marchedData[1];
                        } else {
                            if(Str::contains($txn[2],"UPI CREDIT")) {
                                $utrNumber = trim(str_replace("UPI CREDIT","",$txn[2]));
                            }
                        }

                        $amount = str_replace(",", "", trim($txn[8]));
                        $_amount = (float)$amount;

                        if(isset($utrNumber) && !empty($utrNumber)) {
                            if (!Str::contains(strtolower($txn[2]), "bulkpe")) {
                                $bankTransactionData = new BankTransactionData();
                                $bankTransactionData->accountNumber = $accountNumber;
                                $bankTransactionData->paymentUtr    = trim($utrNumber);
                                $bankTransactionData->amount        = $_amount;
                                $bankTransactionData->bankName      = "FED";
                                $bankTransactionData->paymentMode   = "UPI";
                                $bankTransactionData->udf3          = trim($utrNumber);
                                $bankTransactionData->description   = trim($txn[2]);
                                $bankTransactionData->udf4          = trim($txn[2]);
                                $bankTransactionData->udf5          = trim($txn[2]);
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
                        };
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
            (new BankStatementFile())->markAsError($id);
            (new BankStatementFile())->setRemark($id, $ex->getMessage());
        }
    }
}
