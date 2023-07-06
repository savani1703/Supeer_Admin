<?php

namespace App\Plugin\ManualPayout\ICICI;


use App\Plugin\ManualPayout\Utils\BankTransferResponse;
use App\Plugin\ManualPayout\Utils\BankTransferStatusResponse;
use App\Plugin\ManualPayout\Utils\PayoutStatusResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ICICIModule
{
    public function generateFile($batchId, $payoutMeta, $payoutData) {
        return $this->prepareDataForAdhocFile($batchId, $payoutMeta->debit_account, $payoutData);
    }

    public function parseFile($payoutMeta, $fileData) {
        try {

            // Convert String to array
            $stringArray = explode("\n", $fileData);

            $labelKeys = explode("~", $stringArray[0]);

            // Unset Label String
            unset($stringArray[0]);

            // Remove Empty Line
            $stringArray = array_filter($stringArray, function($a) {
                return trim($a) !== "";
            });

            $statusCheckData = [];

            foreach ($stringArray as $data) {
                if(isset($data) && !empty($data)) {
                    $_array = explode("~", $data);
                    $tempData = [];
                    foreach ($_array as $key => $_data) {
                        $tempData[
                        $this->from_camel_case(str_replace(" ", "", trim($labelKeys[$key])))
                        ] = $_data;
                    }
                    $statusCheckData[] = $tempData;
                }
            }

            $bankTransferStatusResponse = new BankTransferStatusResponse();
            $bankTransferStatusResponse->debitAccountNumber = $payoutMeta->debit_account;
            $bankTransferStatusResponse->bankName = "ICICI";
            $bankTransferStatusResponse->bankResponseData = $this->parseStatusData($statusCheckData);
            return $bankTransferStatusResponse;
        } catch (\Exception $ex) {
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

    private function prepareDataForAdhocFile($batchId, $debitAccountNumber, $payoutData)
    {
        $bankTransferResponse = new BankTransferResponse();

        $totalAmount = array_sum(array_map(function($item) {
            return $item['payout_amount'];
        }, $payoutData->toArray()));

        $totalPayout = sizeof($payoutData);
        $fileDate = Carbon::now("Asia/Kolkata")->format("m/d/Y");
        $adhocData = "FHR|0011|$debitAccountNumber|INR|$totalAmount|$totalPayout|$fileDate|$batchId^\n";

        foreach ($payoutData as $Item) {
            $payoutType = $this->getPayoutTypeForWithinBank($Item->bank_name, $Item->payout_type);
            $ifsc_code=$Item->ifsc_code;
            if(strcmp($payoutType, "WITHIN_BANK") === 0)
            {
                $ifsc_code="ICIC0000011";
            }
            $adhocData .=   $this->getAPCode($payoutType)."|".
                $this->getPayoutType($payoutType, $Item->payout_amount)."|".
                $Item->payout_amount.
                "|INR|".
                $debitAccountNumber."|".
                "0011|".
                $ifsc_code."|".
                $Item->bank_account."|".
                "0011|".
                $Item->account_holder_name."|".
                $Item->payout_id."|".
                $Item->payout_id."^\n";
        }

        $bankTransferResponse->batchId = $batchId;
        $bankTransferResponse->fileName = "$batchId-ICICI-$debitAccountNumber.txt";
        $bankTransferResponse->bankName = "ICICI";
        $bankTransferResponse->payoutAmount = $totalAmount;
        $bankTransferResponse->payoutCount = $totalPayout;
        $bankTransferResponse->debitAccountNumber = $debitAccountNumber;
        $bankTransferResponse->fileData = $adhocData;

        return $bankTransferResponse;
    }

    private function parseStatusData($data) {
        $responseData = [];
        foreach ($data as $_data) {
            $pgPayoutResponse = new PayoutStatusResponse();
            $pgPayoutResponse->pgName = "ICICI";
            $pgPayoutResponse->payoutId = $_data['transaction_remarks'] ?? null;
            $pgPayoutResponse->pgPayoutId = $_data['transaction_remarks'] ?? null;
            $pgPayoutResponse->bankUtr = $_data['host_reference_number'] ?? null;
            $pgPayoutResponse->pgResponseCode = $_data['transaction_status_remarks'] ?? null;
            $pgPayoutResponse->pgResponseMessage = trim($_data['beneficiary_lei']) ?? null;
            $pgPayoutResponse->amount = $this->getAmount($_data['total_amount']) ?? null;
            $pgPayoutResponse->status = $this->getPayoutStatus($_data['transaction_status']) ?? null;
            $pgPayoutResponse->pgResponse = json_encode($_data);
            $responseData[] = $pgPayoutResponse;
        }
        return $responseData;
    }

    private function getAmount($string) {
        $amountObj = explode("|", $string);
        if(isset($amountObj[1])) {
            return $amountObj[1];
        }
        return null;
    }

    private function getPayoutStatus($pgStatus) {
        if(strcmp($pgStatus, 'SUC') === 0) {
            return 'Success';
        }
        if(strcmp($pgStatus, 'FAL') === 0) {
            return 'Failed';
        }
        return 'Pending';
    }

    private function getPayoutType($payoutType, $amount)
    {
        if(floatval($amount) > 500000) {
            $payoutType = "RTGS";
        }
        return (new ICICITransactionType())->getTransactionType($payoutType);
    }

    private function getAPCode($payoutType)
    {
        return strcmp($payoutType, "WITHIN_BANK") === 0 ? "APW" : "APO";
    }

    private function getPayoutTypeForWithinBank($bank, $payoutType) {
        $ptype = $payoutType;
        if(Str::contains(strtolower($bank), "icici")) {
            $ptype = "WITHIN_BANK";
        }
        return $ptype;
    }

    private function from_camel_case($input) {
        $pattern = '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!';
        preg_match_all($pattern, $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ?
                strtolower($match) :
                lcfirst($match);
        }
        return implode('_', $ret);
    }
}
