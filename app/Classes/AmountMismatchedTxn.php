<?php

namespace App\Classes;

class AmountMismatchedTxn
{

    public $transaction_id;
    public $merchant_order_id;
    public $customer_id;
    public $payment_amount;
    public $order_amount;
    public $payment_utr;
    public $payment_tmp_utr;
    public $transaction_date;
    public $payment_status;
    public $bank_txn_date;
    public $upi_id;
}
