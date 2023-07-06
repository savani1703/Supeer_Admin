<?php

namespace App\Http\Controllers;

use App\Models\PaymentManual\LateSuccess;
use App\Models\PaymentManual\PayoutManualRecon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LateSuccessController extends Controller
{

    public function renderview() {
        return view('late-success');
    }

    public function getLateSuccessData(Request $request) {
        $validator = Validator::make($request->all(), [
            'filter_data' => 'nullable|array',
            'page_no' => 'required',
            'limit' => 'required',
        ]);

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return response()->json(['status' => false, 'message' => $error])->setStatusCode(400);
        }
        $data = (new LateSuccess())->getLateSuccessData($request->filter_data, $request->page_no, $request->limit);
        if(isset($data)) {
            $result['data'] = $data->items();
            $result['status'] = true;
            $result['message'] = 'late Success Details Retrieve successfully';
            $result['current_page'] = $data->currentPage();
            $result['last_page'] = $data->lastPage();
            $result['is_last_page'] = !$data->hasMorePages();
            $result['total_item'] = $data->total();
            $result['current_item_count'] = $data->count();

            return response()->json($result)->setStatusCode(200);
        }
        $error['status'] = false;
        $error['message'] = "late Success Not found";
        return response()->json($error)->setStatusCode(400);
    }
}
