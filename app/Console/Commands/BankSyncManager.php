<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Classes\Util\TimeCode;
use App\Models\Management\MerchantPaymentMeta;
use App\Models\Management\PgDown;
use App\Models\PaymentManual\AvailableBank;
use App\Models\PaymentManual\BankConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BankSyncManager extends Command
{

    protected $signature = 'BankSyncManager';
    protected $description = 'Command description';
    public function handle()
    {
        try {
            while (true){
                $activeMerchantBank = (new MerchantPaymentMeta())->getSpecificActiveMerchantList();
                if(isset($activeMerchantBank) && !empty($activeMerchantBank)){
                    foreach ($activeMerchantBank as $_activeMerchantBank){
                        if(isset($_activeMerchantBank->pg_id) && !empty($_activeMerchantBank->pg_id)){
                            $availableBank = (new AvailableBank())->getPayInMetaForBankSync($_activeMerchantBank->pg_id);
                            if(isset($availableBank) && !empty($availableBank)){
                                $isBankDown = (new BankConfig())->checkBankIsDown($availableBank->bank_name);
                                if(!$isBankDown) {
                                    $currentTime = Carbon::now();
                                    $lastBankSync = Carbon::parse($availableBank->last_bank_sync);
                                    $diffInMinutes = $currentTime->diffInMinutes($lastBankSync);
                                    echo "Bank Name : " . $availableBank->account_holder_name . "Diff In min : " . $diffInMinutes . "\n";
                                    if ($diffInMinutes >= 15) {
                                        (new PgDown())->setError('HIDE', 'HIDE', "HIDE", $availableBank->account_holder_name, 'Bank Sync Late Please Check Minute : ' . $diffInMinutes, "LATE_BANK_SYNC", TimeCode::LATE_BANK_SYNC);
                                    }
                                }else{
                                    echo  "\n Bank Is Down";
                                }
                            }
                        }
                    }
                    echo "Wait For 10 Sec \n";
                    sleep(10);
                }else{
                    echo "Wait For 60 Sec \n";
                    sleep(60);
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
            sleep(1);
        }
    }
}
