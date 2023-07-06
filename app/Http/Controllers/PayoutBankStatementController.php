<?php

namespace App\Http\Controllers;

use App\Classes\Util\BankStatementUtils;
use App\Classes\Util\PayoutBankStatementUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PayoutBankStatementController extends Controller
{
    public function uploadStatementFile(Request $request){
        $validator = Validator::make($request->all(), [
            'account_file' => 'required|file',
        ]);

        if($validator->fails()){
            $error = $validator->errors()->first();
            return response()->json(['status' => false,'message' => $error])->setStatusCode(400);
        }

        $fileName       = $request->file('account_file')->getClientOriginalName();
        $accountFile    = file_get_contents($request->file('account_file')->getRealPath());
        return (new PayoutBankStatementUtils())->uploadStatementFile($fileName, $accountFile);
    }

    public function getPayoutStatement(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no'       => 'required',
            'limit'         => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PayoutBankStatementUtils())->getPayoutStatement($request->filter_data, $request->limit, $request->page_no);
    }
    public function showAddedUtr(Request $request) {
        $validator = Validator::make($request->all(), [
            'file_name'         => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new PayoutBankStatementUtils())->showAddedUtr($request->file_name);
    }
}
