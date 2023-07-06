<?php

namespace App\Console\Commands;

use App\Http\Controllers\ManualPayoutController;
use App\Models\Management\PgRouter;
use App\Plugin\ManualPayout\ManualPayout;
use Illuminate\Console\Command;

class PayoutManualFileCompiler extends Command
{
    protected $signature = 'PayoutManualFileCompiler';
    protected $description = 'Command description';
    public function handle()
    {
        try {

            /*$file='D:\omac\1298705 (2).txt';
            $fileContent = file_get_contents($file);
            $bankPayoutMeta = $this->getPayoutMeta("IC002", "ICICI");
            $bankResponse = (new ManualPayout())->payoutStatus("ICICI", $bankPayoutMeta, $fileContent);
            if(isset($bankResponse)) {
                if(isset($bankResponse->bankResponseData)) {
                    (new ManualPayoutController())->updatePayoutData($bankResponse->bankResponseData);
                }
            }*/

        }catch (\Exception $ex){
            dd($ex->getFile());
        }
    }

    private function getPayoutMeta($pgId, $bankName) {
        $pgRouters = (new PgRouter())->getRouterByPg($bankName);
        if(isset($pgRouters)) {
            if(isset($pgRouters->payout_meta_router)) {
                return (new $pgRouters->payout_meta_router)->getPayoutMetaById($pgId);
            }
        }
        return null;
    }
}
