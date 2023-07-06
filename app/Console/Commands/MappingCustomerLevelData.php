<?php

namespace App\Console\Commands;

use App\Models\Management\CustomerLevel;
use App\Models\PaymentManual\CustomerSuccessUpiMapping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MappingCustomerLevelData extends Command
{
    protected $signature = 'MappingCustomerLevelData';
    protected $description = 'Command description';
    public function handle()
    {
        try {
            while (true){
                $this->upiMap();
                echo "sleep for mid 1 sec \n";
                sleep(1);
            }
        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            echo "Exception \n";
            sleep(1);
        }
    }

    private function upiMap()
    {
        $customerLevelDetails = (new CustomerLevel())->getCustomerLevelingDetailsForUpiMap();
        if(isset($customerLevelDetails) && !empty($customerLevelDetails)){
            foreach ($customerLevelDetails as $_customerLevelDetails){
                $totalUpi = (new CustomerSuccessUpiMapping())->getTotalUpi($_customerLevelDetails->customer_id);
                if($totalUpi){
                    echo "total success upi : ".$totalUpi ."customer id : ".$_customerLevelDetails->customer_id."\n";
                    (new CustomerLevel())->updateTotalSuccessUpiId($_customerLevelDetails->customer_id, $totalUpi);
                }
                usleep(10);
            }
        }
    }
}
