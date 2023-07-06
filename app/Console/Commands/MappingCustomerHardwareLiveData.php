<?php

namespace App\Console\Commands;

use App\Models\Management\CustomerHidMappingDetails;
use App\Models\Management\risk\CustHidDetails;
use App\Models\Management\Transactions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MappingCustomerHardwareLiveData extends Command
{
    protected $signature = 'mappingCustomerHardwareLiveData';
    protected $description = 'Command description';
    public function handle()
    {
        try {
            while (true) {
                $customerDetails = (new CustomerHidMappingDetails())->getPendingMapDetails();
                if (isset($customerDetails) && !empty($customerDetails)) {
                    foreach ($customerDetails as $_customerDetails) {
                        if (isset($_customerDetails->device_id) && !empty($_customerDetails->device_id)) {
                            $totalCustomerId = (new Transactions())->checkDeviceHasMultipleCustomer($_customerDetails->device_id);
                            if ($totalCustomerId) {
                                $result = (new CustHidDetails())->setTotalCustomerId($_customerDetails->device_id, $totalCustomerId);
                                if ($result) {
                                    echo "device id : " . $_customerDetails->device_id . " total customer id : " . $totalCustomerId . "\n";
                                }
                            }
                        }
                        usleep(10);
                        (new CustomerHidMappingDetails())->markAsUsed($_customerDetails->id);
                    }
                }
                sleep(2);
                echo "sleep for safety 2 sec\n";
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
}
