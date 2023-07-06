<?php

namespace App\Http\Controllers;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\SupportUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupportController extends Controller
{
    public function getSupportLogs(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->getSupportLogs($request->filter_data, $request->limit, $request->page_no);
    }

    public function getWebhookEvents(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->getWebhookEvents($request->filter_data, $request->limit, $request->page_no);
    }

    public function getPaymentMethods(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->getPaymentMethods($request->filter_data, $request->limit, $request->page_no);
    }

    public function addPaymentMethod(Request $request) {
        $validator = Validator::make($request->all(), [
            'pg_method_id' => 'required',
            'pg_name' => 'required',
            'meta_code' => 'required',
            'method_name' => 'required',
            'method_code' => 'required',
            'is_seamless' => 'required',
            'has_sub_method' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->addPaymentMethod($request->pg_method_id, $request->pg_name, $request->meta_code, $request->method_name, $request->method_code, $request->is_seamless, $request->has_sub_method);
    }

    public function addAvailableMethod(Request $request) {
        $validator = Validator::make($request->all(), [
            'method_name' => 'required',
            'method_icon_url' => 'required',
            'sub_method_icon_url' => 'required'
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->addAvailableMethod($request->method_name, $request->method_icon_url, $request->sub_method_icon_url);
    }

    public function getAvailableMethods(Request $request) {
        return (new SupportUtils())->getAvailableMethods();
    }

    public function getCustomers(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->getCustomers($request->filter_data, $request->page_no, $request->limit);
    }
    public function getCustomersUpiMapDetailsById(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new SupportUtils())->getCustomersUpiMapDetailsById($request->customer_id);
    }
    public function getCustomersStateDetailsById(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new SupportUtils())->getCustomersStateDetailsById($request->customer_id);
    }

    public function updateCustomerBlockStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'pk' => 'required',
            'pg_method' => 'required',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new SupportUtils())->updateCustomerBlockStatus($request->customer_id, $request->pk, $request->pg_method, $request->value);
    }

    public function getPgWebhooks(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }

        return (new SupportUtils())->getPgWebhooks($request->filter_data, $request->page_no, $request->limit);
    }

    public function generateReport(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'report_type' => 'required|in:TRANSACTION,PAYOUT,BANK_TRANSACTION,BLOCK_INFO',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $filterData = DigiPayUtil::parseFilterData($request->filter_data);
        if(isset($filterData['start_date']) && isset($filterData['end_date'])) {
            $diffInDays = Carbon::parse($filterData['start_date'])->diffInDays(Carbon::parse($filterData['end_date']));

            $allowedDiffInDaysReport = env("ALLOWED_REPORT_DAYS", 31);

            if ($diffInDays > $allowedDiffInDaysReport) {
                return response()->json(['status' => false, 'message' => "Date Range is to long"])->setStatusCode(400);
            }
        }

        return (new SupportUtils())->generateReport($request->filter_data, $request->report_type);
    }

    public function getGeneratedReport(Request $request) {
        $validator = Validator::make($request->all(), [
            'page_no'       => 'required',
            'limit'         => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new SupportUtils())->getGeneratedReport($request->limit, $request->page_no);
    }

    public function getBlockInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no'       => 'required',
            'limit'         => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new SupportUtils())->getBlockInfo($request->filter_data, $request->limit, $request->page_no);
    }

    public function deleteBlockInfo(Request $request) {
        $validator = Validator::make($request->all(), [
            'block_data' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new SupportUtils())->deleteBlockInfo($request->block_data);
    }

    public function getBankSync(Request $request) {
        return (new SupportUtils())->getBankSync();
    }
    public function getMobileSync(Request $request) {
        return (new SupportUtils())->getMobileSync();
    }

}
