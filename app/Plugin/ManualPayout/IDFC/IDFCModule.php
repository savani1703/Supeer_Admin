<?php

namespace App\Plugin\ManualPayout\IDFC;

use App\Classes\Util\PgName;
use App\Plugin\ManualPayout\Utils\BankTransferResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IDFCModule
{
    public function generateFile($batchId, $payoutMeta, $payoutData) {
        try {
            $debitAccountNumber = $payoutMeta->debit_account;

            $bankTransferResponse = new BankTransferResponse();

            $totalAmount = array_sum(array_map(function ($item) {
                return $item['payout_amount'];
            }, $payoutData->toArray()));

            $totalPayout = sizeof($payoutData);

            $fileData = [];

            foreach ($payoutData as $Item) {
                $payoutType = $this->getPayoutTypeForWithinBank($Item->bank_name, "NEFT");
                $ifscCode = $Item->ifsc_code;
                if (strcmp($payoutType, "WITHIN_BANK") === 0) {
                    $payoutType = "IFT";
                    $ifscCode = "";
                }
                $fileData[] = [
                    "payout_id" => $Item->payout_id,
                    "batch_id" => $Item->manual_pay_batch_id,
                    "bank_holder" => $Item->account_holder_name,
                    "to_account_number" => $Item->bank_account,
                    "ifsc" => $ifscCode,
                    "payout_type" => $payoutType,
                    "from_account_number" => $debitAccountNumber,
                    "payout_amount" => $Item->payout_amount
                ];
            }

            $bankTransferResponse->batchId = $batchId;
            $bankTransferResponse->fileName = "$batchId-IDFC-$debitAccountNumber.xlsx";
            $bankTransferResponse->bankName = PgName::IDFC;
            $bankTransferResponse->payoutAmount = $totalAmount;
            $bankTransferResponse->payoutCount = $totalPayout;
            $bankTransferResponse->debitAccountNumber = $debitAccountNumber;
            $bankTransferResponse->fileData = $fileData;

            return $bankTransferResponse;
        }catch (\Exception $ex) {
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return null;
        }
    }

    private function getPayoutTypeForWithinBank($bank, $payoutType) {
        $ptype = $payoutType;
        if(Str::contains(strtolower($bank), "idfc")) {
            $ptype = "WITHIN_BANK";
        }
        return $ptype;
    }

}
