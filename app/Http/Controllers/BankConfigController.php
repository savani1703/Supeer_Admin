<?php

namespace App\Http\Controllers;

use App\Classes\Util\BankConfigUtils;
use App\Classes\Util\PgType;
use App\Classes\Util\TransactionUtils;
use App\Models\Management\MerchantDetails;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use App\Plugin\AccessControl\Utils\AccessModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankConfigController extends Controller
{

    public function fetchStatus(){
        return (new BankConfigUtils())->fetchStatus();
    }

    public function updateBankStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'value' =>   'in:0,1',
            'bank_name' =>   'in:FEDERAL,HDFC,ICICI,RBL',
        ]);
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        return (new BankConfigUtils())->updateBankStatus($request->bank_name, $request->value);
    }

}
