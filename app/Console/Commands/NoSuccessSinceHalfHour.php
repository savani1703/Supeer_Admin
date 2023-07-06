<?php

namespace App\Console\Commands;

use App\Classes\Util\PaymentStatus;
use App\Constant\PayoutStatus;
use App\Models\Management\Transactions;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NoSuccessSinceHalfHour extends Command
{
    protected $signature = 'NoSuccessSinceHalfHour';
    protected $description = 'Command description';
    private $telegramApiToken = "";
    private $channelId = "";
    private $noIncidentCount = 0;

    public function handle()
    {

        $message = "📣 <strong>Transaction System Alert</strong> \n\n Last Success At \n ".Carbon::now()->toDateTimeString()." \n Please Check Transactions";
        $this->sendMessage($message);
        while (true)
        {
            $this->getTransactionSystemDetection();
            echo "\n Sleep For 30 Seconds";
            sleep(30);
        }
    }

    private function getTransactionSystemDetection()
    {
        $lasttxnCreated= (new Transactions())->orderBy('created_at','desc')->first();
        $lasttxnCreatedDate=Carbon::parse($lasttxnCreated->created_at);
        $deffinminutesCreated=\Carbon\Carbon::now()->diffInMinutes($lasttxnCreatedDate);
       $lasttxn= (new Transactions())->where("payment_status", PaymentStatus::SUCCESS)->orderBy('success_at','desc')->first();
       $lastSuccess=Carbon::parse($lasttxn->success_at);
       $deffinminutes=\Carbon\Carbon::now()->diffInMinutes($lastSuccess);
       if($deffinminutes > 30 && $deffinminutesCreated<35)
       {
          if($this->noIncidentCount < 6)
          {
              $this->noIncidentCount++;
              $message = "📣 <strong>Transaction System Alert</strong> \n\n Last Success At \n ".$lasttxn->success_at_ist." \n Please Check Transactions";
              $this->sendMessage($message);
          }
          else
          {
              echo "\n Sleep For 300 Seconds too many Alert";
              sleep(300);
              $this->noIncidentCount = 0;
          }
       }else
       {
           echo "\n NoIncidentCount: $this->noIncidentCount \n";
           echo "\n No Incident detect";
       }
    }
    private function sendMessage($message, $isDisableSoundNotification = false) {
        try {

        } catch (\InvalidArgumentException | ConnectException $ex) {
            echo $ex->getMessage() . "\n";
            echo "Failed to send Alert \n";
        } catch (GuzzleException $ex) {
            echo "Failed to send Alert \n";
            print_r($ex->getResponse()->getBody()->getContents()) . "\n";
        }
    }
}
