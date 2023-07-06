<?php

namespace App\Plugin\ManualPayout\Utils;

class BankTransferResponse
{
    public $batchId;
    public $fileName;
    public $payoutCount;
    public $payoutAmount;
    public $bankName;
    public $debitAccountNumber;
    public $fileData;
}
