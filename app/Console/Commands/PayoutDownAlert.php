<?php

namespace App\Console\Commands;

use App\Constant\PayoutStatus;
use App\Models\Management\Payout;
use App\Models\Management\PayoutConfig;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class PayoutDownAlert extends Command
{
    protected $signature = 'PayoutDownAlert';

    private $telegramApiToken = "";
    private $channelId = "";
    private $noIncidentCount = 0;
    public function handle()
    {
        while (true)
        {
            $this->noIncidentCount++;
            $this->getPayoutSystemDetection();
            echo "\n Sleep For 600 Seconds";
            sleep(600);
        }
    }

    private function getPayoutSystemDetection() {
        $payoutConfig = (new PayoutConfig())->loadConfig();
        $pendingCount = (new Payout())->where("payout_status", PayoutStatus::PENDING)->count();
        $lowbalCount = (new Payout())->where("payout_status", PayoutStatus::LOWBAL)->count();
        $lasthour=Carbon::now()->subHours(2);
        $lastFailedData = (new Payout())->where(function ($q) {
            $q->where("payout_status", PayoutStatus::SUCCESS);
            $q->orWhere("payout_status", PayoutStatus::FAILED);
        })->where('created_at','>',$lasthour)
            ->select("payout_status")
            ->orderBy("created_at", "desc")
            ->take($payoutConfig->max_last_failed_limit)
            ->get();

        $isLastFailedLimitExceed = false;
        $isLastFailedCount = 0;
        if($lastFailedData->count() > 0 && $lastFailedData->count() >= $payoutConfig->max_last_failed_limit) {
            if($lastFailedData->where("payout_status", PayoutStatus::FAILED)->count() === $lastFailedData->count()) {
                $isLastFailedLimitExceed = true;
                $isLastFailedCount = $lastFailedData->where("payout_status", PayoutStatus::FAILED)->count();
            }
        }


        $totalInitPayoutCount = (new Payout())->where(function ($q) {
            $q->where("payout_status", PayoutStatus::INITIALIZED);
            $q->orWhere("payout_status", PayoutStatus::LOWBAL);
        })->orderBy("created_at", "desc")->count();

        $isAutoPayoutSystemDisabled = $payoutConfig->is_auto_transfer_enable === 1;
        $isPendingLimitExceed = $pendingCount >= $payoutConfig->max_pending_limit;
        $isLowBalLimitExceed = $lowbalCount >= $payoutConfig->max_lowbal_limit;

        $this->getPayoutSystemAlertMessage(
            $isLastFailedCount,
            $isLastFailedLimitExceed,
            $isAutoPayoutSystemDisabled,
            $isLowBalLimitExceed,
            $isPendingLimitExceed,
            $totalInitPayoutCount
        );

    }

    private function getPayoutSystemAlertMessage(
        $isLastFailedCount,
        $isLastPayoutFailed,
        $isAutoPayoutSystemDisable,
        $isLowbalLimitExceed,
        $isPendingLimitExceed,
        $totalInitPayoutCountWithAutoMinMaxLimit
    ) {
        if(
            $isLastPayoutFailed ||
            $isAutoPayoutSystemDisable ||
            $isPendingLimitExceed ||
            $isLowbalLimitExceed)
        {
            $message = "📣 <strong>Payout System Alert</strong> \n\n";

            if($isLowbalLimitExceed) {
                $message .= "▪ <u>LOWBAL Limit Exceed Detected</u> \n\n";
            }
            if($isPendingLimitExceed) {
                $message .= "▪ <u>Pending Limit Exceed Detected</u> \n\n";
            }
            if($isLastPayoutFailed) {
                $message .= "▪ <u> Last Failed System Detected (Count: $isLastFailedCount)</u> \n\n";
            }
            if($isAutoPayoutSystemDisable) {
                $message .= "▪ <u>Auto Payout System Stop Detected</u> \n\n";
            }
            if($totalInitPayoutCountWithAutoMinMaxLimit > 0) {
                $message .= "▪ <u>Total Init Payout: $totalInitPayoutCountWithAutoMinMaxLimit</u> \n\n";
            }
            // dd($message);
            $this->sendMessage($message);
        } else {
            if($this->noIncidentCount == 6) {
                echo "NoIncidentCount: $this->noIncidentCount \n";
                $message = "📣 <strong>Payout System Stop Alert</strong> \n\n";
                $message .= "No Incident detect in last 60 Min";
                $this->sendMessage($message);
                $this->noIncidentCount = 0;
            }
            echo "No Incident Detect \n";
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
