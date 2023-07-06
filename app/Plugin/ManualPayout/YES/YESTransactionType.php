<?php

namespace App\Plugin\ManualPayout\YES;

class YESTransactionType
{
    const NEFT = "NEFT";
    const IMPS = "IFT";

    function getTransactionType($transactionType) {
        return constant("self::$transactionType");
    }

}
