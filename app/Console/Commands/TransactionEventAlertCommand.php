<?php

namespace App\Console\Commands;

use App\Classes\Util\TelegramUtils;
use App\Models\Management\TransactionEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransactionEventAlertCommand extends Command
{
    protected $signature = 'TransactionEventAlert';
    protected $description = 'Transaction Event Alert';
    protected $cnt = 0;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle() {
        $this->cnt++;
        if($this->cnt>100)
        {
            exit(0);
        }else
        {
            echo "\n". $this->cnt;
        }
        $eventList = $this->getTxnEvent();
        if(isset($eventList)) {
            foreach ($eventList as $event) {
                $message = "<b><u>Multiple Webhook Alert</u></b> \n \n";
                $message .= "<b>EventId: </b> $event->event_id \n";
                $message .= "<b>Event Count: </b> $event->event_count \n";
                (new TelegramUtils())->sendPgDownAlert($message);
                $this->markAsGet($event->event_id);
                echo "Event Sent To TG \n";
            }
        } else {
            echo "No Multiple Webhook Sent \n";
        }
        sleep(10);
        $this->handle();
    }

    private function getTxnEvent() {
        try {
            $startDate = Carbon::now()->format("Y-m-d 00:00:00");
            $endDate = Carbon::now()->format("Y-m-d 23:59:59");

            $txns = (new TransactionEvent())
                ->select(DB::raw("count(1) as event_count"), "event_id")
                ->whereBetween("created_at", [$startDate, $endDate])
                ->where("is_get", 0)
                ->where("webhook_status_code", "200")
                ->groupBy("event_id")
                ->orderBy("event_count", "desc")
                ->get();
            if($txns->count() > 0) {
                $txnList = [];
                foreach ($txns as $_txns) {
                    if($_txns->event_count >= 2) {
                        $txnList[] = $_txns;
                    }
                }
                if(sizeof($txnList) > 0) {
                    return $txnList;
                }
            }
            return null;
        } catch (\Exception $ex) {
            return null;
        }
    }

    private function markAsGet($eventId) {
        try {
            (new TransactionEvent())->where("event_id", $eventId)->update([
                "is_get" => 1
            ]);
        } catch (\Exception $ex) {

        }
    }
}
