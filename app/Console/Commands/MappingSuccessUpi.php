<?php

namespace App\Console\Commands;

use App\Models\Management\BankTransactions;
use App\Models\Management\CustomerLevel;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\CustomerSuccessUpiMapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MappingSuccessUpi extends Command
{
    protected $signature = 'mappingSuccessUpi';
    protected $description = 'Command description';

    public function handle()
    {
        try {
            while (true){
                $bankTransaction = (new BankTransactions())->getUsedTransactionList();
                if(isset($bankTransaction) && !empty($bankTransaction)){
                    foreach ($bankTransaction as $_bankTransaction){
                        $transactionDetails = (new Transactions())->getTransactionByUTRForMap($_bankTransaction->payment_utr);
                        if (isset($transactionDetails) && !empty($transactionDetails)){
                            if(
                                isset($_bankTransaction->upi_id) && !empty($_bankTransaction->upi_id) &&
                                isset($transactionDetails->merchant_id) && !empty($transactionDetails->merchant_id) &&
                                isset($transactionDetails->customer_id) && !empty($transactionDetails->customer_id) &&
                                isset($transactionDetails->payment_data) && !empty($transactionDetails->payment_data)
                            ){
                                $merchantId     = $transactionDetails->merchant_id;
                                $customerId     = $transactionDetails->customer_id;
                                $successUpiId   = $_bankTransaction->upi_id;
                                $upiId          = $transactionDetails->payment_data;
                                $result = (new CustomerSuccessUpiMapping())->addCustomerSuccessUpiMap($merchantId, $customerId, $successUpiId, $upiId);
                                if($result){
                                    (new CustomerLevel())->increaseTotalSuccessUpiId($customerId, $merchantId);
                                    echo "Data added ( Enter Upi ) :- ".$upiId." ( Success Upi Id ) :-". $successUpiId."\n";
                                }
                            }
                        }
                        (new BankTransactions())->markAsGet($_bankTransaction->id);
                    }
                }
                echo "Wait For 1 Sec \n";
                sleep(10);
            }
        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            sleep(10);
            echo "Wait For 10 Sec \n";
        }
    }
}
