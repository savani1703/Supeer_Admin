<?php

namespace App\Classes;

use App\Classes\Util\PaymentStatus;
use App\Models\Management\BankTransactions;
use App\Models\Management\Transactions;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

class TransactionUtrAndAmountChecker
{
    public function checkTransactionForSuccess($transactionId,$utrNumber)
    {
        try {
            $transaction = new Transactions();
            $res = $transaction->getTransactionById($transactionId);
            if (!isset($res)) {
                return;
            }
            $txnInfo = (new BankTransactions())->checkUtrAndAmount($utrNumber, $res->payment_amount);
            if (isset($txnInfo) && !empty($txnInfo)) {
                if (isset($txnInfo->description)) {
                    if (Str::contains(strtolower($txnInfo->description), "bulkpe")) {
                        (new BankTransactions())->markAsUsed($utrNumber);
                        return;
                    }
                }
                if ($txnInfo->isget == 0) {
                    $result = $transaction->transactionMarkAsSuccessForUpi($txnInfo, $transactionId);
                    if ($result) {
                        (new BankTransactions())->transactionMakeAsUsed($txnInfo->id);
                        \Illuminate\Support\Facades\Log::info('TXN Success', [
                            'transaction_id' => $transactionId,
                            'UTR' => $txnInfo->payment_utr,
                            'payment_status' => PaymentStatus::SUCCESS,
                            'success_at' => $txnInfo->created_at,
                            'bank_rrn' => $txnInfo->payment_utr
                        ]);
                    }
                } else {
                    $result = $transaction->transactionMarkAsFailedForUpi($txnInfo, $transactionId);
                    if ($result) {
                        \Illuminate\Support\Facades\Log::info('TXN Failed', [
                            'transaction_id' => $transactionId,
                            'UTR' => $txnInfo->payment_utr,
                            'payment_status' => PaymentStatus::SUCCESS,
                            'success_at' => $txnInfo->created_at,
                            'bank_rrn' => $txnInfo->payment_utr
                        ]);
                    }
                }
            }
        } catch (QueryException $ex) {
            \Illuminate\Support\Facades\Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }

    }
}
