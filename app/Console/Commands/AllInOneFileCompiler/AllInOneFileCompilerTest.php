<?php

namespace App\Console\Commands\AllInOneFileCompiler;

use App\Classes\BankFileCompiler\BankStatementManager;
use App\Imports\UsersImport;
use App\Models\PaymentManual\BankStatementFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AllInOneFileCompilerTest extends Command
{
    protected $signature = 'AllInOneFileCompilerTest';
    protected $description = 'Command description';
    public function handle()
    {
        $id = null;
        try {
            ini_set('memory_limit', '-1');
            while (true) {
                $bankFileDetails = (new BankStatementFile())->getUnParseBankFiles();
                echo "Parsing Start \n";
                if (!isset($bankFileDetails) || empty($bankFileDetails)) {
                    echo "sleep for 1 sec";
                    sleep(1);
                    continue;
                }

                $transactionArray = null;
                if (isset($bankFileDetails->file_name) && !empty($bankFileDetails->file_name)) {
                    $transactionArray = Excel::toArray(new UsersImport, $bankFileDetails->file_name, 's3-file');
                }

                if (!isset($transactionArray) || empty($transactionArray)) {
                    echo "sleep for 1 sec";
                    sleep(1);
                    continue;
                }
                $fileName = $bankFileDetails->file_name;
                (new BankStatementManager())->parseManager($id, $fileName, $transactionArray);
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
