<?php

namespace App\Console\Commands\Payout;

use App\Classes\Util\PayoutUtils;
use App\Models\Management\Payout;
use App\Models\Management\PayoutCustomerLevel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PayoutLevelV1 extends Command
{

    protected $signature = 'PayoutLevelV1';
    protected $description = 'Command description';

    public function handle()
    {
        try {
            while (true) {
                $payout = (new PayoutCustomerLevel())->getDetails();
                if (isset($payout) && !empty($payout)) {
                    foreach ($payout as $_payout) {
                        echo "\n start : " . $_payout->customer_id;

                        if (isset($_payout->customer_id) && !empty($_payout->customer_id) && isset($_payout->account_number) && !empty($_payout->account_number)) {
                            $accountNumber = $_payout->account_number;
                            $customerId = $_payout->customer_id;

                            (new PayoutUtils())->setCustomerPayoutLevelDataV1($_payout,'PAYOUT_SYNC_V1');

                            $result1 = (new PayoutCustomerLevel())->markAsGet($customerId);
                            if ($result1) {
                                echo "\n make as sync get : " . $accountNumber . " customerId : " . $customerId;
                            } else {
                                echo "\n make as sync get Skipped..... " . $accountNumber . " customerId : " . $customerId;
                            }
                        }else{
                            echo "\n Wait For 1000 Sec Data Not Found ";
                            sleep(1000);
                        }

                        echo "Wait For 40 Micro Sec \n";
                        usleep(40);
                    }
                }
                echo "Wait For 10 Sec \n";
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
            echo "Error Wait \n";
            sleep(10);
        }
    }
}
