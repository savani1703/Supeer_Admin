<?php

namespace App\Console\Commands;

use App\Models\Management\BatchTransfer;
use App\Models\Management\Payout;
use App\Models\Management\PayoutManualReconciliation;
use App\Models\Management\PgRouter;
use App\Plugin\ManualPayout\ManualPayout;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeCustomFile extends Command
{
    protected $signature = 'MakeCustomFile';
    protected $description = 'Command description';

    public function handle()
    {
        try {

            $array = [
                '6885256651','7322411161','5328672579','6890482244','6344133527','1130756922','7387359812','7147647841','9627749697','9494976139','3473149528','5424653515','7876843844','8251155776','9393674235','3695523429','9244134487','4858994678','4294516122','7178489198','3498276829','7722311384','2319751944','8927386927','6025783260','3814699873','5526341460','2283772812','2523445182','4010800454','5189799787','3946441389','4639488969','4320093756','8177864437','8854143241','7315648613','7852936478'
            ];

            (new PayoutManualReconciliation())->markAsUsed($array);
            /*$metaId = "IC006";
            $bankName = "ICICI";*/

            $metaId = "YES001";
            $bankName = "YES";

            /*$metaId = "ID007";
            $bankName = "IDFC";*/

            $bankTransferMeta = $this->getPayoutMeta($metaId, $bankName);
            $payoutListForManualPayout = (new Payout())->getPayoutForBatchTransferForCustom($array);

            $totalBatchAmount = 0;
            $totalBatchRecord = 0;

            foreach ($payoutListForManualPayout as $_payoutListForManualPayout){
                $totalBatchAmount = $totalBatchAmount + floatval($_payoutListForManualPayout->payout_amount);
                $totalBatchRecord++;
            }
            //dd($totalBatchAmount, $totalBatchRecord);
            $batchId = strtoupper(Str::random(10));
            if((new BatchTransfer())->checkBatchIsExist($batchId)) {
                return response()->json(['status' => false, 'message' => "System Error Please try again after some time"])->setStatusCode(400);
            }

            $generatedFileData = (new ManualPayout())->initPayout($batchId, $bankName, $bankTransferMeta, $payoutListForManualPayout);
            //dd($generatedFileData->fileData);
            $debitAccount = $bankTransferMeta->debit_account;
            (new BatchTransfer())->addManualPayoutBatchCustom($batchId, $metaId, $bankName, $debitAccount, $totalBatchAmount, $totalBatchRecord, $generatedFileData->fileData, $generatedFileData->fileName);
            (new Payout())->updateManualPayoutMetaDetails($array, $debitAccount, $metaId, $bankName, $batchId);
            //dd("OK");

            //dd($generatedFileData->fileData);

        }catch (\Exception $ex){
            dd($ex->getMessage());
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
