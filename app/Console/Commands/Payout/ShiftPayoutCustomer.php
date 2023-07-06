<?php

namespace App\Console\Commands\Payout;

use App\Models\Management\PayoutCustomerLevel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ShiftPayoutCustomer extends Command
{

    protected $signature = 'shiftPayoutCustomer';
    protected $description = 'Command description';

    public function handle()
    {
        try {

                $payoutDetails = (new PayoutCustomerLevel())->getDetailsForShift();
                if (isset($payoutDetails) && !empty($payoutDetails)) {
                    foreach ($payoutDetails as $key => $_payoutDetails) {
                        echo "\n start : " . $_payoutDetails->customer_id . "KEY : " . $key;
                        if (isset($_payoutDetails->customer_id) && !empty($_payoutDetails->customer_id)) {
                            $result = (new PayoutCustomerLevel())->customerShift($_payoutDetails->customer_id);
                            if ($result) {
                                echo "\n customer shifted : " . $_payoutDetails->customer_id;
                            } else {
                                echo "\n Skipped...........  " . $_payoutDetails->customer_id;
                            }
                        }
                    }
                }

        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            echo "Error Wait \n";
            sleep(10);
        }
    }
}
