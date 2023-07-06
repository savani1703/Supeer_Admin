<?php

namespace App\Plugin\ManualPayout\Utils;

class BankRouter
{
    const ICICI = "App\\Plugin\\ManualPayout\\ICICI\\ICICIModule";
    const IDFC = "App\\Plugin\\ManualPayout\\IDFC\\IDFCModule";
    const YES = "App\\Plugin\\ManualPayout\\YES\\YESModule";

    function routeToFileGenerate($batchId, $bankName, $payoutMeta, $payoutData) {
        $bankRouter = constant("self::$bankName");
        return (new $bankRouter)->generateFile($batchId, $payoutMeta, $payoutData);
    }

    function routeToParseFile($bankName, $payoutMeta, $fileData) {
        $bankRouter = constant("self::$bankName");
        return (new $bankRouter)->parseFile($payoutMeta, $fileData);
    }

}
