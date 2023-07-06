<?php

namespace App\Plugin\AccessControl\Utils;

class AccessModule
{
    const ACCESS_DENIED  = "Access Denied";

    // Transaction
    const BANK_TRANSACTION_REPORT           = "A0037";

    // Transaction
    const TRANSACTION_AUTO_VIEW             = "A0002";
    const TRANSACTION_MANUAL_VIEW           = "A0003";
    const TRANSACTION_REPORT                = "A0004";
    const TRANSACTION_ACTION                = "A0005";
    const TRANSACTION_SUMMARY               = "A0042";

    // Payout
    const PAYOUT_AUTO_VIEW                  = "A0007";
    const PAYOUT_MANUAL_VIEW                = "A0008";
    const PAYOUT_REPORT                     = "A0009";
    const PAYOUT_MODIFY                     = "A0010";
    const PAYOUT_SUMMARY                    = "A0111";

    // Reconciliation
    const TRANSACTION_RECONCILIATION        = "A0013";
    const PAYOUT_RECONCILIATION             = "A0014";

    // Merchant
    const MERCHANT_VIEW                     = "A0015";
    const MERCHANT_MODIFY                   = "A0016";
    const MERCHANT_ADD_PAY_IN_OUT           = "A0116";
    const MERCHANT_PAYIN_META_VIEW          = "A0017";
    const MERCHANT_PAYIN_META_MODIFY        = "A0018";
    const MERCHANT_PAYOUT_META_VIEW         = "A0019";
    const MERCHANT_PAYOUT_META_MODIFY       = "A0020";
    const BANK_CONFIG                       = "A0043";

    //settlement
    const MERCHANT_RELEASE_SETTLEMENT       = "A0041";

    // Payment Gateway Meta
    const PG_PAYIN_META_VIEW                = "A0021";
    const PG_PAYIN_META_MODIFY              = "A0022";
    const PG_PAYOUT_META_VIEW               = "A0023";
    const PG_PAYOUT_META_MODIFY             = "A0024";

    // Developer

    const DEVELOPER_MODULE                  = "A0035";
// ROLE 3
    const MANUAL_BANK_ENTRY                 ="A00100";
// ROLE 5
    const SMS_READER                        ="A00101";
    const MOBILE_SYNC                        ="A1125";
}
