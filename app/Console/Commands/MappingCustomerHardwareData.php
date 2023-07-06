<?php

namespace App\Console\Commands;

use App\Models\Management\risk\CustHidDetails;
use App\Models\Management\Transactions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MappingCustomerHardwareData extends Command
{
    protected $signature = 'mappingCustomerHardwareData';
    protected $description = 'Command description';
    public function handle()
    {
        /*try {
            $customerHidDetails = (new CustHidDetails())->getCustomerHidData();
            if(isset($customerHidDetails) && !empty($customerHidDetails)){
                foreach ($customerHidDetails as $_customerHidDetails){
                    $totalCustomerId = (new Transactions())->checkDeviceHasMultipleCustomer($_customerHidDetails->device_id);
                    if($totalCustomerId){
                        $result = (new CustHidDetails())->setTotalCustomerId($_customerHidDetails->device_id, $totalCustomerId);
                        if($result){
                            echo "device id : ".$_customerHidDetails->device_id. " total customer id : ". $totalCustomerId ."\n";
                        }
                    }
                    usleep(10);
                    (new CustHidDetails())->setMarkAsUsed($_customerHidDetails->device_id);
                }
                sleep(5);
                echo "sleep for safety 5 sec\n";
                (new CustHidDetails())->resetAllGetData();
                sleep(1800);
                echo "sleep for rest 30 min\n";
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
        }*/
    }
}
