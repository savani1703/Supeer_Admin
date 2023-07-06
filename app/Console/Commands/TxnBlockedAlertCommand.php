<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Models\Management\PgDown;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TxnBlockedAlertCommand extends Command
{
    protected $signature = 'TransactionBlockAlert';
    protected $description = 'Transaction Block Alert';
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

        $pendingNotice = (new PgDown())->getPendingBlockNotice();
        if(isset($pendingNotice)) {
            $message = "<strong>New PG Down Alert</strong> ". $pendingNotice->created_at . "\n\n";

            if(Str::contains($pendingNotice->txt_exception, "blocked_by_vpn")) {
                $message = "<strong>Block By VPN Alert</strong> ". $pendingNotice->created_at . "\n\n";
            }
            if(Str::contains($pendingNotice->txt_exception, "App Blocked")) {
                $message = "<strong>Block By App Alert</strong> ". $pendingNotice->created_at . "\n\n";
            }
            if(Str::contains($pendingNotice->txt_exception, "Operation timed out")) {
                $message = "<strong>Gateway Down Alert</strong> ". $pendingNotice->created_at . "\n\n";
            }
            if(Str::contains($pendingNotice->pg_name, "isCustomerDataBlocked")) {
                $message = "<strong>Blocked By Customer Details Alert</strong> ". $pendingNotice->created_at . "\n\n";
            }
            if(strcmp($pendingNotice->data_type, "UPIPAYSRISK") === 0) {
                $message = "<strong>Risk Threshold exceeded</strong> ". $pendingNotice->created_at . "\n\n";
            }

            $message .= "<strong>PG:</strong> ". $pendingNotice->pg_name . "\n";
            $message .= "<strong>PG MID:</strong> ". $pendingNotice->pg_mid."\n";
            $message .= "<strong>Transaction Id:</strong> ". $pendingNotice->transaction_id."\n";
            $message .= "<strong>Reason:</strong> \n". "<code>".$pendingNotice->reason."</code> \n\n";
            $message .= "<strong>Exception:</strong> \n". "<pre>".$pendingNotice->txt_exception."</pre> \n\n";
            if(strcmp($pendingNotice->data_type, "UPIPAYSRISK") === 0) {
                (new TelegramUtils())->sendUPIAlert($message);
            } else {
                (new TelegramUtils())->sendBlockAlert($message, true);
            }
            (new PgDown())->markAsGet($pendingNotice->id);
            echo "Notice Sent, Wait For 2 Sec \n";
            sleep(2);
            $this->handle();
        } else {
            echo "No Pending Notice Found, Wait For 10 Sec \n";
            sleep(10);
            $this->handle();
        }
    }

}
