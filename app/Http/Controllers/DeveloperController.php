<?php

namespace App\Http\Controllers;

use App\Classes\Util\DeveloperUtils;
use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\PgMetaUtils;
use App\Models\PaymentManual\AvailableBank;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DeveloperController extends Controller
{
    public function renderDashboardView(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return view('developer.dashboard');
    }

    public function renderBouncerView(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $payInPgList = DigiPayUtil::PayInPgList();
        return view('developer.dashboard.bouncer')->with("payInPgList", $payInPgList);
    }

    public function getPgRouters(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return (new DeveloperUtils())->getPgRouters();
    }

    public function getProxyList(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return (new DeveloperUtils())->getProxyList();
    }
    public function getBankProxyListWithoutLogin(Request $request) {

        return (new DeveloperUtils())->getBankProxyList($request->acc_number);
    }

    public function getBankProxyList(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return (new DeveloperUtils())->getBankProxyList();
    }

    public function renderPayInSummaryView(Request $request) {
        $merchantList = DigiPayUtil::MerchantList();
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return view("developer.dashboard.payin-summary")->with("merchantList", $merchantList);
    }

    public function addProxy(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'proxy_ip' => 'required',
            'label' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new DeveloperUtils())->addProxy($request->label, $request->proxy_ip);
    }

    public function addBankProxy(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'ip_proxy' => 'required',
            'label_name' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new DeveloperUtils())->addBankProxy($request->label_name, $request->ip_proxy);
    }
    public function bankProxyDelete(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->bankProxyDelete($request->id);
    }
    public function editBankProxyStatus(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->editBankProxyStatus($request->pk,$request->value);
    }
    public function getBouncerData(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new DeveloperUtils())->getBouncerData($request->filter_data, $request->page_no, $request->limit);
    }

    public function getPgPayInSummary(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new DeveloperUtils())->getPgPayInSummary($request->merchant_id, $request->start_date, $request->end_date);
    }

    public function getPayoutDownBanks(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return (new DeveloperUtils())->getPayoutDownBanks();
    }

    public function deleteByIdPayoutDownBank(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'list_id' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->deleteByIdPayoutDownBank($request->list_id);
    }

    public function deletePayoutDownBank(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return (new DeveloperUtils())->deletePayoutDownBank();
    }

    public function getSMSLogs(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->getSMSLogs($request->filter_data, $request->page_no, $request->limit);
    }
    public function getIdfcWebhook(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->getIdfcWebhook($request->filter_data, $request->page_no, $request->limit);
    }

    public function getMailReader(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->getMailReader($request->filter_data, $request->page_no, $request->limit);
    }

    public function updateMailReaderStatus(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->updateMailReaderStatus($request->pk, $request->value);
    }

    public function addMailReader(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'bank_id' => 'required',
            'username' => 'required',
            'password' => 'required',
            'mail_sender' => 'required',
            'mail_from' => 'required',
            'provider' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->addMailReader($request->bank_id, $request->username, $request->password, $request->mail_sender, $request->mail_from, $request->provider);
    }

    public function getPayoutWhiteListClient(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        return (new DeveloperUtils())->getPayoutWhiteListClient();
    }

    public function updatePayoutWhiteListClientStatus(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->updatePayoutWhiteListClientStatus($request->merchant_id, $request->status);
    }
    public function updatePayoutWhiteListClientStatusManual(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string',
            'is_manual_payout' => 'required|in:0,1',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->updatePayoutWhiteListClientStatusManual($request->merchant_id, $request->is_manual_payout);
    }

    public function addPayoutWhiteListClient(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'merchant_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->addPayoutWhiteListClient($request->merchant_id);
    }
    public function editClientWhiteListLimit(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $validator = Validator::make($request->all(), [
            'pk' => 'required',
            'value' => 'required|numeric',
            'column_name' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new DeveloperUtils())->editwhitelistlimit($request->pk,$request->value,$request->column_name);
    }

    public function renderSwiftCustView(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::DEVELOPER_MODULE)) {
            return new Response(view("error.401"));
        }
        $merchantList = DigiPayUtil::MerchantList();
        $data = (new AvailableBank())->getAvailableBank($bankName='');
        return view('developer.dashboard.swift-customer')
            ->with("merchantList", $merchantList)
            ->with("bankList", $data);
        }
}
