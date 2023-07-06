<?php

namespace App\Console\Commands\BankFileCompiler;

use App\Classes\BankFileCompiler\BankStatementManager;
use App\Imports\UsersImport;
use App\Models\PaymentManual\BankStatementFile;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AllInOneFileCompiler extends Command
{

    protected $signature = 'AllInOneFileCompiler';
    protected $description = 'Command description';
    public function handle()
    {
        $id = null;
        try {
            ini_set('memory_limit', '-1');
            while (true) {
                $checkFileInQueue = (new BankStatementFile())->checkFileIsRunning();
                if($checkFileInQueue){
                    echo "File Already In Queue \n";
                    exit();
                }

                $bankFileDetails = (new BankStatementFile())->getUnParseBankFiles();

                echo "Parsing Start \n";

                if (!isset($bankFileDetails) || empty($bankFileDetails)) {
                    sleep(1);
                    echo "sleep for 1 sec";
                    exit();
                }

                $id = $bankFileDetails->id;
                (new BankStatementFile())->markAsRunning($id);

                $transactionArray = null;
                if (isset($bankFileDetails->file_name) && !empty($bankFileDetails->file_name)) {
                    $transactionArray = Excel::toArray(new UsersImport, $bankFileDetails->file_name, 's3-file');
                }

                if (!isset($transactionArray) || empty($transactionArray)) {
                    sleep(1);
                    echo "sleep for 1 sec";
                    exit();
                }

                $fileName = $bankFileDetails->file_name;

                (new BankStatementManager())->parseManager($id, $fileName, $transactionArray);
                (new BankStatementFile())->markAsUsed($id);

                echo "No Pending Parse File, Wait For 10 Sec \n";
                sleep(10);
            }

        }catch (\Exception $ex){
            (new BankStatementFile())->markAsError($id);
            (new BankStatementFile())->setRemark($id, 'EXCEPTION_IN_COMMANDS');
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
