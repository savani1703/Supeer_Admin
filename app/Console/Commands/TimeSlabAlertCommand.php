<?php

namespace App\Console\Commands;

use App\Models\Management\Payout;
use App\Models\Management\PgDown;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TimeSlabAlertCommand extends Command
{
    protected $signature = 'TimeSlabAlertCommand';
    protected $description = 'Command description';

    public function handle()
    {
        try {
            $this->payoutAlertHandler();
        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            echo "Exception \n";
            print_r($ex->getMessage());
            sleep(1);
        }
    }

    private function payoutAlertHandler()
    {
        try {
            $payoutDetails = (new Payout())->getInitPayoutWithClient();
            foreach ($payoutDetails as $_payoutDetails){
                if(isset($_payoutDetails->merchant_id) && !empty($_payoutDetails->merchant_id)){
                    $created_at = Carbon::parse($_payoutDetails->created_at);
                    $diffInMin  = Carbon::now()->diffInMinutes($created_at);
                    $isValidToAdd = false;
                    if($diffInMin === 5){
                        $isValidToAdd = true;
                    }
                    if($diffInMin === 10){
                        $isValidToAdd = true;
                    }
                    if($isValidToAdd){
                        //$count = (new PgDown())->check
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
            echo "Exception \n";
            print_r($ex->getMessage());
            sleep(1);
        }
    }
}
