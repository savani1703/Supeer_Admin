<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Models\Management\MerchantDetails;
use App\Models\Management\TransactionNotice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class NoticeAlertCommand extends Command
{
    protected $signature = 'NoticeAlert';
    protected $description = 'Notice Alert';
    protected $cnt = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        while (true) {
            echo "\nchecking at " . Carbon::now();
            $pendingNotice = (new TransactionNotice())->getPendingNotice();
            if (isset($pendingNotice)) {
                $merchantName = (new MerchantDetails())->where("merchant_id", $pendingNotice->merchant_id)->first("merchant_name");
                $message = "<strong>New Alert</strong> " . $pendingNotice->created . "\n\n";
                $message .= "<strong>Merchant:</strong> " . isset($merchantName) ? $merchantName->merchant_name . "(" . $pendingNotice->merchant_id . ") \n" : $pendingNotice->merchant_id . "\n";
                $message .= "<strong>Browser Id:</strong> " . $pendingNotice->bwsr_id . "\n";
                $message .= "<strong>Transaction Id:</strong> " . $pendingNotice->transaction_id . "\n";
                $message .= "<strong>Reason:</strong> \n" . "<code>" . $pendingNotice->reson . "</code>";
                (new TelegramUtils())->sendAlert($message);
                (new TransactionNotice())->markAsGet($pendingNotice->id);
                echo "\n Notice Sent, Wait For 2 Sec \n";
                sleep(2);
            } else {
                echo "\n No Pending Notice Found, Wait For 10 Sec \n";
                sleep(10);
            }
        }
    }

}
