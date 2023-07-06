<?php

namespace App\Classes\Util;

use App\Models\CashFree\CashFreeMeta;
use App\Models\EaseBuzz\EaseBuzzPayinMeta;
use App\Models\EaseBuzz\EaseBuzzPayoutMeta;
use App\Models\HealthCheckData;
use App\Models\NuPayMeta;
use App\Models\NupayPayout;
use App\Models\Omniware\OmniwareMeta;
use App\Models\Omniware\OmniwarePayoutMeta;
use App\Models\PayG\PayGMeta;
use App\Models\PaykunMeta;
use App\Models\PaytmMeta;
use App\Models\PaytmPayout;
use App\Models\PayU\PayUMeta;
use App\Models\Qr\QrMeta;
use App\Models\RazorPay\RazorPayMeta;
use App\Models\RazorPay\RazorPayPayoutMeta;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class HealthCheck
{

    public function performCheck() {
        try {
            $pgDomainList = [];
            $pgIpList = [];

            // $healthCheckData = (new HealthCheckData())->getHealthCheckData();
            if(isset($healthCheckData)) {
                foreach ($healthCheckData as $data) {
                    if(isset($data->bouncer_domain)) {
                        $pgDomainList[] = $data->bouncer_domain;
                    }
                    if(isset($data->bouncer_ip)) {
                        $pgIpList[] = $data->bouncer_ip;
                    }
                }
            }


            $pgDomainList = array_unique($pgDomainList);
            $pgIpList = array_unique($pgIpList);
            if(sizeof($pgDomainList) > 0) $this->healthCheckDomain($pgDomainList);
            if(sizeof($pgIpList) > 0) $this->healthCheckIp($pgIpList);
            return true;
        } catch (\Exception $ex) {
            return true;
        }
    }

    private function healthCheckDomain($domainList) {

        foreach ($domainList as $webUrl) {
            echo "\n".$webUrl;
            $domain = $this->getDomainFromUrl($webUrl);
            if(isset($domain) && !empty($domain)) {
                $ports = ["80", "443"];
                foreach ($ports as $port) {
                    echo "\n checking ".$domain ." ".$port ;
                    $connection = @fsockopen($domain, $port);
                    if (is_resource($connection)) {
                        //(new HealthCheckData())->updateUrlCheckDate($webUrl, true);
                        echo "\n Works Fine  ".$domain ." ".$port ;
                        fclose($connection);
                    } else {
                        echo "Bouncer Server Domain is Not Responding: $webUrl \n";
                        // (new HealthCheckData())->updateUrlCheckDate($webUrl, false);
                        $this->sendPgUrlDownAlert($webUrl, $domain, $port);
                    }
                }
            }
        }
    }

    private function healthCheckIp($ips) {
        foreach ($ips as $host) {
            $ipDetail = parse_url($host);
            if(isset($ipDetail)) {
                $ip = $ipDetail["host"];
                $port = $ipDetail["port"];

                try {
                    echo "\n checking ".$ip ." ".$port ;
                    $client = new Client([
                        'base_uri'  => "https://example.com",
                        'proxy' => $host
                    ]);
                    $client_response = $client->get("/");
                    echo "\n Works Fine  ".$ip ." ".$port ;
                    //(new HealthCheckData())->updateIpCheckDate($host, true);
                } catch (\InvalidArgumentException | ConnectException | GuzzleException $ex) {
                    echo "Bouncer Server IP is Not Responding: $host  \n";
                    //(new HealthCheckData())->updateIpCheckDate($host, false);
                    $this->sendPgIpDownAlert($host, $ip, $port, $ex->getMessage());
                }
            }
        }
    }

    private function getDomainFromUrl($url) {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return null;
    }

    private function sendPgIpDownAlert($host, $ip, $port, $exMsg) {
        $message = "<b>Bouncer Server IP is Not Responding</b> \n";
        $message .= "<b>Date</b> : ".Carbon::now("Asia/Kolkata")." \n";
        $message .= "<b>IP</b> : $ip \n";
        $message .= "<b>Port</b> : $port \n";
        $message .= "<b>Error</b> : \n<code>$exMsg</code> \n \n";
        $message .= "<b>Associate PG</b> : \n <code>" . $this->getAssociatedPg("bouncer_ip", $host)."</code>";
        (new TelegramUtils())->sendPgDownAlert($message);
    }

    private function sendPgUrlDownAlert($webUrl, $domain, $port) {
        $message = "<b>Bouncer Server Domain is Not Responding</b> \n";
        $message .= "<b>Date</b> : " . Carbon::now("Asia/Kolkata") . " \n";
        $message .= "<b>Protocol</b> : " . getservbyport($port, 'tcp') . " \n";
        $message .= "<b>Domain</b> : $domain \n";
        $message .= "<b>Port</b> : $port \n\n";
        $message .= "<b>Associate PG : \n<code>" . $this->getAssociatedPg("bouncer_domain", $webUrl)."</code>";
        (new TelegramUtils())->sendPgDownAlert($message);
    }

    public function getAssociatedPg($field, $value) {
//        try {
//            $message = "";
//            $data = (new HealthCheckData())->getPgListByFieldForAlert($field, $value);
//            if(isset($data)) {
//                foreach ($data as $item) {
//                    $message .= "$item->pg ($item->pg_label - $item->pg_id): {$item->type} \n";
//                }
//            } else {
//                $message = "Associated PG Not Found";
//            }
//            return $message;
//        } catch (\Exception $ex) {
//            return "NA at Exception: ".$ex->getMessage();
//        }
    }

}
