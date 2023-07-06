<?php

namespace App\Classes\Util;

use App\Models\Management\Refund;
use Illuminate\Support\Facades\Log;

class RefundUtils
{
    public function getRefund($filterData, $limit, $pageNo){
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $refundData = (new Refund())->getRefund($filterData, $limit, $pageNo);
            if(isset($refundData)) {
                $result['status'] = true;
                $result['message'] = 'Refund Details Retrieve successfully';
                $result['current_page'] = $refundData->currentPage();
                $result['last_page'] = $refundData->lastPage();
                $result['is_last_page'] = !$refundData->hasMorePages();
                $result['total_item'] = $refundData->total();
                $result['current_item_count'] = $refundData->count();
                $result['data'] = $refundData->items();
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Refund Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Error while getting Refund";
            Log::error('Error in Exception', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            return response()->json($error)->setStatusCode(500);
        }
    }
}
