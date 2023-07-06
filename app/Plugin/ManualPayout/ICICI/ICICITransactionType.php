<?php

namespace App\Plugin\ManualPayout\ICICI;

class ICICITransactionType
{
    const WITHIN_BANK = "WIB";
    const NEFT = "NFT";
    const RTGS = "RTG";
    const IMPS = "IFC";

    function getTransactionType($transactionType) {
        return constant("self::$transactionType");
    }
}
