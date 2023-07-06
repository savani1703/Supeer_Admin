<?php

namespace App\Http\Controllers;

use App\Classes\Util\BankStatementUtils;
use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\SupportUtils;
use App\Models\Management\BankStatement;
use App\Models\PaymentManual\AvailableBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankStatementController extends Controller
{
    public function renderRoute() {
        $availableBank = (new AvailableBank())->getAllActivePgMeta();
        return view('bank-statement')->with("availableBank", $availableBank);
    }

    public function uploadStatementFile(Request $request){
        $validator = Validator::make($request->all(), [
            'account_file' => 'required|file|mimes:xls,xlsx',
        ]);

        if($validator->fails()){
            $error = $validator->errors()->first();
            return response()->json(['status' => false,'message' => $error])->setStatusCode(400);
        }

        $fileName       = $request->file('account_file')->getClientOriginalName();
        $accountFile    = file_get_contents($request->file('account_file')->getRealPath());
        return (new BankStatementUtils())->uploadStatementFile($fileName, $accountFile);
    }

    public function getStatement(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no'       => 'required',
            'limit'         => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new BankStatementUtils())->getStatement($request->filter_data, $request->limit, $request->page_no);
    }

    public function showAddedUtr(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'         => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new BankStatementUtils())->showAddedUtr($request->id);
    }
}
