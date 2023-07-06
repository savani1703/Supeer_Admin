<?php

namespace App\Classes\Util;

use App\Models\Bouncer\BouncerData;
use App\Models\Management\BankProxy;
use App\Models\Management\Payout;
use App\Models\Management\PayoutBankDown;
use App\Models\Management\PayoutWhiteListClient;
use App\Models\Management\PgRouter;
use App\Models\Management\ProxyList;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\IDFC\IdfcMailWebhook;
use App\Models\PaymentManual\MailReader;
use App\Models\PaymentManual\SMSLogs;
use Carbon\Carbon;

class DeveloperUtils
{

    public function getPgRouters()
    {
        try {
            $pgRouters = (new PgRouter())->getAllPgMetaModels();
            if(isset($pgRouters)) {
                $result['status'] = true;
                $result['message'] = "PG Routers Data Retrieved";
                $result['data'] = $pgRouters;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "PG Routers Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getProxyList()
    {
        try {
            $proxyList = (new ProxyList())->getProxyList();
            if(isset($proxyList)) {
                $result['status'] = true;
                $result['message'] = "PG Proxy Data Retrieved";
                $result['data'] = $proxyList;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "PG Proxy Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getBankProxyList($acc_number=null)
    {
        try {
            $bankProxyList = (new BankProxy())->getBankProxyList($acc_number);

            if(isset($bankProxyList)) {
                $result['status'] = true;
                $result['message'] = "Bank Proxy Data Retrieved";
                $result['data'] = $bankProxyList;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Bank Proxy Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addProxy($label, $proxyIp)
    {
        try {
            if((new ProxyList())->checkIsExists($label, $proxyIp)) {
                SupportUtils::logs('PROXY',"New Proxy Added, Proxy: $proxyIp, Label: $label");
                $error['status'] = false;
                $error['message'] = "PG Proxy Data Already Available";
                return response()->json($error)->setStatusCode(400);
            }
            if((new ProxyList())->addProxy($label, $proxyIp)) {
                $result['status'] = true;
                $result['message'] = "PG Proxy Data Added";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to add PG Proxy Data ";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addBankProxy($label, $proxyIp)
    {
        try {
            if((new BankProxy())->checkIsExists($label, $proxyIp)) {
                SupportUtils::logs('PROXY',"New Proxy Added, Proxy: $proxyIp, Label: $label");
                $error['status'] = false;
                $error['message'] = "Bank Proxy Data Already Available";
                return response()->json($error)->setStatusCode(400);
            }
            if((new BankProxy())->addBankProxy($label, $proxyIp)) {
                $result['status'] = true;
                $result['message'] = "Bank Proxy Data Added";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to add Bank Proxy Data ";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getBouncerData($filterData, $pageNo, $limit)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $bouncerData = (new BouncerData())->getBouncerData($filterData, $pageNo, $limit);
            if(isset($bouncerData)) {
                $result = DigiPayUtil::withPaginate($bouncerData);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Bouncer Data Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPayoutDownBanks()
    {
        try {
            $payoutBankDownData = (new PayoutBankDown())->getAllDownBankList();
            if (isset($payoutBankDownData)) {
                $response = [];
                foreach ($payoutBankDownData as $bank) {
                    $banksPayoutSummary = (new Payout())->getPayoutSummaryByBank($bank->bank_name);
                    $response[] = [
                        "id" => $bank->id,
                        "bank_name" => $bank->bank_name,
                        "ifsc_prefix" => $bank->ifsc_prefix,
                        "is_down" => $bank->is_down,
                        "last_down_at" => Carbon::parse($bank->last_down_at)->setTimezone('Asia/Kolkata')->format("d-m-Y H:i:s"),
                        "total_count" => isset($banksPayoutSummary) ? $banksPayoutSummary->total_count : 0,
                        "total_amount" => isset($banksPayoutSummary) ? round($banksPayoutSummary->total_amount) : 0,
                    ];
                }
                $result['status'] = true;
                $result['message'] = "Payout Bank Down Data Retrieved";
                $result['data'] = $response;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Bank Down Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function deleteByIdPayoutDownBank($listId)
    {
        try {
            if((new PayoutBankDown())->deleteByBank($listId)) {
                SupportUtils::logs('PAYOUT BANK DOWN',"Payout Down Bank Delete, BankID: $listId");
                $result['status'] = true;
                $result['message'] = "Payout Bank Down Data Deleted";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to delete Payout Bank Down Data";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }
    public function bankProxyDelete($id)
    {
        try {
            if((new BankProxy())->deleteBankProxy($id)) {
                SupportUtils::logs('BANK PROXY DELETE',"Bank Proxy  Delete, ID: $id");
                $result['status'] = true;
                $result['message'] = "Bank Proxy Deleted";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to delete Bank Proxy";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function editBankProxyStatus($id,$status)
    {
        try {
            if((new BankProxy())->editBankProxyStatus($id,$status)) {
                SupportUtils::logs('BANK PROXY UPDATE',"Bank Proxy  Update, ID: $id,'Status :',$status,");
                $result['status'] = true;
                $result['message'] = "Bank Proxy Update";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update Bank Proxy";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }
    public function editwhitelistlimit($mid,$value,$columnName)
    {
        try {
            if((new PayoutWhiteListClient())->editwhitelistlimit($mid,$value,$columnName)) {
                SupportUtils::logs('Payout White List Client Limit Update',"$columnName Update, merchant_id: $mid,'Set Limit :',$value");
                $result['status'] = true;
                $result['message'] = "White List Client Limit Update";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to Update White List Client Limit";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function deletePayoutDownBank()
    {
        try {
            if((new PayoutBankDown())->deleteAllBank()) {
                $result['status'] = true;
                $result['message'] = "Payout Bank Down Data Deleted";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Failed to delete Payout Bank Down Data";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPgPayInSummary($merchantId, $startDate, $endDate)
    {
        try {
            $payinData = [];
            $pgPayInSummary = (new Transactions())->getPgPayInSummary($merchantId, $startDate, $endDate);
            if(isset($pgPayInSummary)) {
                foreach ($pgPayInSummary as $key => $_pgPayInSummary) {
                    $payinData[] = [
                        "pg_name" => $_pgPayInSummary->pg_name,
                        "meta_id" => $_pgPayInSummary->meta_id,
                        "label" => $this->getPgAccountLable($_pgPayInSummary->pg_name, $_pgPayInSummary->meta_id),
                        "meta_merchant_id" => $_pgPayInSummary->meta_merchant_id,
                        "payment_status" => $_pgPayInSummary->payment_status,
                        "total_txn" => $_pgPayInSummary->total_txn,
                    ];
                }

                $parsePayinData = [];

                if(isset($payinData)) {
                    foreach ($payinData as $data) {
                        if(isset($parsePayinData[$data['pg_name'].$data['meta_id']])) {
                            $parsePayinData[$data['pg_name'].$data['meta_id']][$data['payment_status']] = $data['total_txn'];
                            $parsePayinData[$data['pg_name'].$data['meta_id']]['total_txn'] = $parsePayinData[$data['pg_name'].$data['meta_id']]['total_txn'] + floatval($data['total_txn']);
                        } else {
                            $parsePayinData[$data['pg_name'].$data['meta_id']] = [
                                $data['payment_status'] => $data['total_txn'],
                                'pg_name' => $data['pg_name'],
                                'meta_id' => $data['meta_id'],
                                'label' => $data['label'],
                                'last_success_txn_date' => (new Transactions())->getLastTxnTimeByMetaId($data['meta_id']),
                                'total_txn' => floatval($data['total_txn']),
                            ];
                        }
                    }
                }

                if(sizeof($parsePayinData) > 0) {
                    $parsePayinData = array_values($parsePayinData);
                    $tempPayinData = [];
                    if(sizeof($tempPayinData) > 0) {
                        usort($tempPayinData, function($a, $b) {
                            return $a['pg_name'] <=> $b['pg_name'] ?: $b['total_txn'] <=> $a['total_txn'];
                        });
                    }

                    $result['status'] = true;
                    $result['message'] = "PG Summary Retrieved";
                    $result['data'] = $parsePayinData;
                    return response()->json($result)->setStatusCode(200);
                }


            }
            $error['status'] = false;
            $error['message'] = "PG Summary Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }


    public function getSMSLogs($filterData, $pageNo, $limit)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $smsLogs = (new SMSLogs())->getLogs($filterData, $pageNo, $limit);
            if(isset($smsLogs)) {
                $result = DigiPayUtil::withPaginate($smsLogs);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "SMS Logs Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getIdfcWebhook($filterData, $pageNo, $limit)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $data = (new IdfcMailWebhook())->getIdfcWebhook($filterData, $pageNo, $limit);
            if(isset($data)) {
                $result = DigiPayUtil::withPaginate($data);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "IDFC Webhook Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getMailReader($filterData, $pageNo, $limit)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $mailReaders = (new MailReader())->getMailReader($filterData, $pageNo, $limit);
            if(isset($mailReaders)) {
                $result = DigiPayUtil::withPaginate($mailReaders);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Mail Reader Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updateMailReaderStatus($bankId, $status)
    {
        try {
            if((new MailReader())->updateReaderStatus($bankId, $status)) {
                $result['status'] = true;
                $result['message'] = "Mail Reader Status Updated";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while update mail reader status";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addMailReader($bankId, $username, $password, $mailSender, $mailFrom, $provider)
    {
        try {
            if((new MailReader())->addMailReader($bankId, $username, $password, $mailSender, $mailFrom, $provider)) {
                $result['status'] = true;
                $result['message'] = "Mail Reader Added";
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Error while add mail reader";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getPayoutWhiteListClient()
    {
        try {
            $data = (new PayoutWhiteListClient())->getAllClientList();
            if(isset($data)) {
                $error['status'] = true;
                $error['message'] = "data retrieved";
                $error['data'] = $data;
                return response()->json($error)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "data not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "data not found";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutWhiteListClientStatus($merchantId, $status)
    {
        try {
            if((new PayoutWhiteListClient())->updateClientStatus($merchantId, $status)) {
                SupportUtils::logs('Payout White List Client Auto Status Update',"Status $status, merchant_id: $merchantId");
                $error['status'] = true;
                $error['message'] = "data updated";
                return response()->json($error)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "failed";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "failed";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function updatePayoutWhiteListClientStatusManual($merchantId, $isManualPayout)
    {
        try {
            if((new PayoutWhiteListClient())->updateClientStatusManual($merchantId, $isManualPayout)) {
                SupportUtils::logs('Payout White List Client Manual Status Update',"Status $status, merchant_id: $merchantId");
                $error['status'] = true;
                $error['message'] = "data updated";
                return response()->json($error)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "failed";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "failed";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function addPayoutWhiteListClient($merchantId)
    {
        try {
            if((new PayoutWhiteListClient())->addClient($merchantId)) {
                $error['status'] = true;
                $error['message'] = "data added";
                return response()->json($error)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "failed";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "failed";
            return response()->json($error)->setStatusCode(500);
        }
    }

    private function getPgAccountLable($pgName, $accountId) {
        if(isset($pgName) && isset($accountId)) {
            $pgRouter = (new PgRouter())->getRouterByPg($pgName);
            if(isset($pgRouter->payin_meta_router)) {
                $pgLabel = (new $pgRouter->payin_meta_router)->getPgLabel($accountId);
                if(isset($pgLabel)) {
                    return $pgLabel;
                }
            }
        }

        return null;
    }
}
