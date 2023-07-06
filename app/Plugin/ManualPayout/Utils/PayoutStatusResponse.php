<?php

namespace App\Plugin\ManualPayout\Utils;

use App\Constant\PayoutStatus;

class PayoutStatusResponse
{

    public $status = PayoutStatus::PENDING;
    public $payoutId;
    public $pgPayoutId;
    public $pgName;
    public $bankUtr;
    public $amount;
    public $pgResponseCode;
    public $pgResponseMessage;
    public $pgResponse;

}
