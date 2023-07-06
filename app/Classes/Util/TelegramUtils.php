<?php

namespace App\Classes\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class TelegramUtils
{
    private $alertDownGroup = "-x";
    private $noticeGroup = "-x";
    private $blockAlert = "-x";
    private $payoutalertOtp = "-x";
    private $txnlertForVpn = "-x";
    private $bankAlert = "-x";

    public function sendAlert($message)
    {
        return $this->sendAlertMessage($message, $this->noticeGroup);
    }

    public function sendPgDownAlert($message)
    {
        return $this->sendMessage($message, $this->alertDownGroup);
    }

    public function sendPayoutDownAlertForOtp($message)
    {
        return $this->sendPayoutMessageForOtp($message, $this->payoutalertOtp);
    }

    public function sendBlockAlert($message, $isSilent = false)
    {
        return $this->sendMessageBlock($message, $this->blockAlert, $isSilent);
    }

    public function sendTxnVpnAlert($message)
    {
        return $this->sendCenterMessage($message, $this->txnlertForVpn);
    }
    public function sendBankSyncAlert($message)
    {
        return $this->sendCenterMessage($message, $this->bankAlert);
    }

    private function sendPayoutMessageForOtp($message, $channelId)
    {

    }

    private function sendCenterMessage($message, $channelId)
    {

    }


    private function sendMessage($message, $channelId)
    {

    }

    private function sendAlertMessage($message, $channelId)
    {

    }

    private function sendMessageBlock($message, $channelId)
    {

    }

}
