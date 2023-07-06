<?php

namespace App\Plugin\ManualPayout\IDFC;

class IDFCTransactionType
{
    const NEFT = "NEFT";
    const IMPS = "IFT";

    function getTransactionType($transactionType) {
        return constant("self::$transactionType");
    }
}
