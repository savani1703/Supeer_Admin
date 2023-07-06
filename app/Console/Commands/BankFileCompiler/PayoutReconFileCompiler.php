<?php

namespace App\Console\Commands\BankFileCompiler;

use App\Classes\BankFileCompiler\PayoutBankStatementManager;
use App\Imports\UsersImport;
use App\Models\PaymentManual\PayoutBankStatementFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PayoutReconFileCompiler extends Command
{
    protected $signature = 'PayoutReconFileCompiler';
    protected $description = 'Command description';

    public function handle()
    {
        $id = null;
        try {

            ini_set('memory_limit', '-1');
            while (true) {
                $checkFileInQueue = (new PayoutBankStatementFile())->checkFileIsRunning();
                if($checkFileInQueue){
                    echo "File Already In Queue \n";
                    sleep(1);
                    continue;
                }

                $bankFileDetails = (new PayoutBankStatementFile())->getUnParseBankFiles();

                echo "Parsing Start \n";

                if (!isset($bankFileDetails) || empty($bankFileDetails)) {
                    echo "sleep for 1 sec";
                    sleep(1);
                    continue;
                }

                $id = $bankFileDetails->id;
                (new PayoutBankStatementFile())->markAsRunning($id);

                $transactionArray = null;
                if (isset($bankFileDetails->file_name) && !empty($bankFileDetails->file_name)) {
                    $transactionArray = Excel::toArray(new UsersImport, $bankFileDetails->file_name, 's3-payout-recon');
                }

                if (!isset($transactionArray) || empty($transactionArray)) {
                    echo "sleep for 1 sec";
                    sleep(1);
                    continue;
                }

                $fileName = $bankFileDetails->file_name;

                (new PayoutBankStatementManager())->parseManager($id, $fileName, $transactionArray);
                (new PayoutBankStatementFile())->markAsUsed($id);

                echo "No Pending Parse File, Wait For 10 Sec \n";
                sleep(10);
            }

        }catch (\Exception $ex){
            (new PayoutBankStatementFile())->markAsError($id);
            (new PayoutBankStatementFile())->setRemark($id, 'EXCEPTION_IN_COMMANDS');
            echo $ex->getMessage();
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
        }
    }
}
