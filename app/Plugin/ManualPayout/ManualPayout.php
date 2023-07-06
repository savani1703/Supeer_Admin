<?php

namespace App\Plugin\ManualPayout;


use App\Plugin\ManualPayout\Utils\BankRouter;

class ManualPayout
{
    public function initPayout($batchId, $bankName, $payoutMeta, $payoutData) {
        return (new BankRouter())->routeToFileGenerate($batchId, $bankName, $payoutMeta, $payoutData);
    }

    public function payoutStatus($bankName, $payoutMeta, $fileData) {
        return (new BankRouter())->routeToParseFile($bankName, $payoutMeta, $fileData);
    }
}
