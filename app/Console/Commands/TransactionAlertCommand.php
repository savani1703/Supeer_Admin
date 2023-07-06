<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Models\Management\Transactions;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransactionAlertCommand extends Command
{

    protected $signature = 'TransactionAlert';
    protected $description = 'Transaction Alert';
    protected $cnt = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->cnt++;
        if($this->cnt>100)
        {
            exit(0);
        }else
        {
            echo "\n". $this->cnt;
        }
        $startDate = Carbon::now()->subMinutes(5)->format("Y-m-d H:i:s");
        $endDate = Carbon::now()->format("Y-m-d H:i:s");

        $transaction = (new Transactions())->whereBetween("created_at", [$startDate, $endDate])
                        ->select("payment_status", DB::raw("count(1) as txn_count"))
                        ->groupBy("payment_status")
                        ->get()->toArray();
        $txnData = [];
        $total = 0;
        if(sizeof($transaction) > 0) {
            foreach ($transaction as $txn) {
                $total = $total + $txn['txn_count'];
                $txnData[$txn['payment_status']] = $txn['txn_count'];
            }
        }

        $messages = [];
        $txnData1 = [];
        if(sizeof($txnData) > 0) {
            foreach ($txnData as $key => $_txn) {
                $txnData1[$key] = round(($_txn * 100) / $total, 2);
            }
        }

        if(sizeof($txnData1) > 0) {
            foreach ($txnData1 as $key1 => $_txn1) {

                if(strcmp($key1, "Failed") === 0) {
                    if($_txn1 >= 30) {
                        $tempMsg = "<b>⭕ High Severity Failed Alert</b> \n";
                        $tempMsg .= "Failed is reach to $_txn1% in $total transaction";
                        $messages[] = $tempMsg;
                    }
                }

//                if(strcmp($key1, "Initialized") === 0) {
//                    if($_txn1 >= 10) {
//                        $tempMsg = "<b style='color: red'>⭕ High Severity Initialized Alert</b>    \n";
//                        $tempMsg .= "Initialized is reach to $_txn1% in $total transaction";
//                        $messages[] = $tempMsg;
//                    }
//                }
            }
        }

        if(sizeof($messages) > 0) {
            foreach ($messages as $message) {
                (new TelegramUtils())->sendAlert($message);
            }
        }

        echo "Wait 5 Minutes for Next Check";
        sleep(300);
        $this->handle();
    }

}
