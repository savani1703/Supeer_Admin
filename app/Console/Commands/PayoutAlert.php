<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Models\Management\PgDown;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PayoutAlert extends Command
{

    protected $signature = 'PayoutAlert';
    protected $description = 'Command description';
    public function handle()
    {
        try {
            while (true) {
                $pendingNotice = (new PgDown())->getPendingPayoutNotice();
                if (isset($pendingNotice)) {
                    $message = "<strong>New PG Down Alert</strong> " . $pendingNotice->created . "\n\n";
                    $message .= "<strong>PG:</strong> " . $pendingNotice->pg_name . "\n";
                    $message .= "<strong>PG MID:</strong> " . $pendingNotice->pg_mid . "\n";
                    $message .= "<strong>Transaction Id:</strong> " . $pendingNotice->transaction_id . "\n";
                    $message .= "<strong>Reason:</strong> \n" . "<code>" . $pendingNotice->reason . "</code> \n\n";
                    $message .= "<strong>Exception:</strong> \n" . "<pre>" . $pendingNotice->txt_exception . "</pre> \n\n";
                    (new TelegramUtils())->sendPayoutDownAlert($message);
                    (new PgDown())->markAsGet($pendingNotice->id);
                    echo "Notice Sent, Wait For 2 Sec \n";
                    sleep(2);
                } else {
                    echo "No Pending Notice Found, Wait For 10 Sec \n";
                    sleep(10);
                }
                sleep(1);
            }
        }catch (\Exception $ex){
            dd($ex->getMessage());
        }
    }
}
