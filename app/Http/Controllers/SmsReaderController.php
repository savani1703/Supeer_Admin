<?php

namespace App\Http\Controllers;

use App\Classes\Util\DeveloperUtils;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class SmsReaderController
{
    public function getSMSLogs(Request $request) {
        if(!(new AccessControl())->hasAccessModule(AccessModule::SMS_READER)) {
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
}
