<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Models\Management\PgDown;
use Illuminate\Console\Command;

class BankSyncAlert extends Command
{
    protected $signature = 'BankSyncAlert';
    protected $description = 'Command description';
    public function handle()
    {
        try {
            while (true) {
                $pendingNotice = (new PgDown())->getPendingNoticeForLateSync();
                if (isset($pendingNotice)) {
                    $message = "<strong>Bank Related Alert</strong> " . $pendingNotice->created . "\n\n";
                    $message .= "<strong>MID:</strong> " . $pendingNotice->pg_mid . "\n";
                    $message .= "<strong>Transaction Id:</strong> " . $pendingNotice->transaction_id . "\n";
                    $message .= "<strong>Name :</strong> \n" . "<pre>" . $pendingNotice->reason . "</pre> \n\n";
                    $message .= "<strong>Exception:</strong> \n" . "<pre>" . $pendingNotice->txt_exception . "</pre> \n\n";
                    (new TelegramUtils())->sendBankSyncAlert($message);
                    (new PgDown())->markAsGet($pendingNotice->id);
                    echo "Notice Sent, Wait For 1 Sec \n";
                    sleep(1);
                } else {
                    echo "No Pending Notice Found, Wait For 10 Sec \n";
                    sleep(10);
                }
            }
        }catch (\Exception $ex){
            echo "No Pending Notice Found, Wait For 10 Sec \n";
            sleep(10);
        }
    }
}
