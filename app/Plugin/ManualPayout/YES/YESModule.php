<?php

namespace App\Plugin\ManualPayout\YES;

use App\Classes\Util\PgName;
use App\Plugin\ManualPayout\Utils\BankTransferResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class YESModule
{

    public function generateFile($batchId, $payoutMeta, $payoutData) {
        return $this->prepareDataForAdhocFile($batchId, $payoutMeta->debit_account, $payoutData);
    }

    private function prepareDataForAdhocFile($batchId, $debitAccountNumber, $payoutData)
    {
        $bankTransferResponse = new BankTransferResponse();

        $totalAmount = array_sum(array_map(function($item) {
            return $item['payout_amount'];
        }, $payoutData->toArray()));

        $totalPayout = sizeof($payoutData);
        $fileDate = Carbon::now("Asia/Kolkata")->format("m/d/Y");
        $adhocData = "SERIAL SERIAL,BENNAME,IMPS,ACCOUNTNUMBER,AMOUNT,IFSC CODE,91BEN PHONENUMBER,DESCRIPTION\n";

        foreach ($payoutData as $key => $Item) {
            $ifsc_code = $Item->ifsc_code;
            $index = $key + 1;
            $adhocData .=
                $index.
                ",".$Item->account_holder_name.",".
                "IMPS,".
                $Item->bank_account.",".
                $Item->payout_amount.",".
                $ifsc_code.",".
                ",".
                $Item->payout_id."\n";
        }

        $bankTransferResponse->batchId = $batchId;
        $bankTransferResponse->fileName = "$batchId-YES-$debitAccountNumber.txt";
        $bankTransferResponse->bankName = "YES";
        $bankTransferResponse->payoutAmount = $totalAmount;
        $bankTransferResponse->payoutCount = $totalPayout;
        $bankTransferResponse->debitAccountNumber = $debitAccountNumber;
        $bankTransferResponse->fileData = $adhocData;

        return $bankTransferResponse;
    }

}
