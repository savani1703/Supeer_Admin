<?php

namespace App\Console\Commands\PDF;

use App\Models\Management\BatchTransfer;
use App\Models\Management\Payout;
use App\Models\Management\PayoutManualReconciliation;
use App\Models\Management\PgRouter;
use App\Plugin\ManualPayout\ManualPayout;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use thiagoalessio\TesseractOCR\TesseractOCR;

class TestPdf extends Command
{

    protected $signature = 'testPdf';
    protected $description = 'Command description';

    public function handle()
    {
        try {
            $array = [

            ];

            $metaId = "ID007";
            $bankName = "IDFC";
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
            //dd($generatedFileData);
            $debitAccount = $bankTransferMeta->debit_account;
            (new BatchTransfer())->addManualPayoutBatchCustomForIDFC($batchId, $metaId, $bankName, $debitAccount, $totalBatchAmount, $totalBatchRecord);
            (new BatchTransfer())->updateBatchFileData($batchId, $generatedFileData->fileData, $generatedFileData->fileName);
            (new Payout())->updateManualPayoutMetaDetails($array, $debitAccount, $metaId, $bankName, $batchId);
            dd("OK");

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
