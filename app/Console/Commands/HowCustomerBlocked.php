<?php

namespace App\Console\Commands;

use App\Models\Management\BlockInfo;
use App\Models\Management\Transactions;
use Illuminate\Console\Command;

class HowCustomerBlocked extends Command
{

    protected $signature = 'hw';
    protected $description = 'Command description';

    public function handle()
    {
        try {
            $transaction = (new Transactions())->getTransactionByBrowserIdForBlock('95VpJzgg5ruQ2A0w5Q6t');
            if(!isset($transaction) || empty($transaction)){
                dd($transaction);
            }
            $blockData = [];
            foreach ($transaction as $_transaction) {
                if (isset($_transaction->customer_id) && !empty($_transaction->customer_id)) {
                    $blockData[] = ["block_data" => $_transaction->customer_id, 'column_name' => 'customer_id', 'merchant_id' => $_transaction->merchant_id];
                    $customerEmailList = (new Transactions())->getAllCustomerEmailListById($_transaction->customer_id);
                    if (isset($customerEmailList) && !empty($customerEmailList)) {
                        foreach ($customerEmailList as $_customerEmailList) {
                            $blockData[] = ["block_data" => $_customerEmailList->customer_email, 'column_name' => 'customer_email', 'merchant_id' => $_transaction->merchant_id];
                        }
                    }
                    $customerMobileList = (new Transactions())->getAllCustomerMobileListById($_transaction->customer_id);
                    if (isset($customerMobileList) && !empty($customerMobileList)) {
                        foreach ($customerMobileList as $_customerMobileList) {
                            $blockData[] = ["block_data" => $_customerMobileList->customer_mobile, 'column_name' => 'customer_mobile', 'merchant_id' => $_transaction->merchant_id];
                        }
                    }
                    $allHidId = (new Transactions())->getAllHid($_transaction->customer_id);
                    if (isset($allHidId) && !empty($allHidId)) {
                        foreach ($allHidId as $hidId) {
                            if (isset($hidId) && !empty($hidId) && strcmp(strtolower($hidId), 'na') !== 0) {
                                $blockData[] = ["block_data" => $hidId, 'column_name' => 'browser_id', 'merchant_id' => $_transaction->merchant_id];
                            }
                        }
                    }
                    $allPaymentData = (new Transactions())->getAllCustomerUpiListById($_transaction->customer_id);
                    if (isset($allPaymentData) && !empty($allPaymentData)) {
                        foreach ($allPaymentData as $_allPaymentData) {
                            $blockData[] = ["block_data" => $_allPaymentData->payment_data, 'column_name' => 'payment_data', 'merchant_id' => $_transaction->merchant_id];
                        }
                    }
                }
            }
            foreach ($blockData as $_blockData){
                $isManually = (new BlockInfo())->checkIsManuallyBlocked($_blockData['block_data']);
                if($isManually){
                    dd($_blockData['block_data']);
                    //(new BlockInfo())->deleteBlockData($_blockData['block_data']);
                    echo $_blockData['block_data']. "\n";
                }
            }
            dd($blockData);
        }catch (\Exception $ex){
            dd($ex->getMessage());
        }
    }
}
