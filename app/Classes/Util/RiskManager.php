<?php

namespace App\Classes\Util;

use App\Http\Controllers\RiskManagementController;
use App\Models\Management\CustomerLevel;
use App\Models\Management\MerchantBalance;
use App\Models\Management\MerchantDetails;
use App\Models\Management\risk\CustHidDetails;
use App\Models\Management\Transactions;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessModule;
use Aws\WAFV2\WAFV2Client;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Query;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Yield_;

class RiskManager
{
    public function getCustSummery($filterData)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $custSummery['Total Customer'] = 0;
            $custSummery['Today\'s Level 1'] = 0;
            $custSummery['Today\'s Level 2'] = 0;
            $custSummery['Today\'s Level 5'] = 0;

            $customerLevel = (new CustomerLevel())->getCustlevel($filterData);
            if (isset($customerLevel)){
                $custSummery['Total Customer'] = $customerLevel->count();
                $custSummery['Today\'s Level 1'] = $customerLevel->where('user_security_level',1)->count();
                $custSummery['Today\'s Level 2'] = $customerLevel->where('user_security_level',2)->count();
                $custSummery['Today\'s Level 5'] = $customerLevel->where('user_security_level',5)->count();
            }
           if(isset($custSummery)) {
                $result['status'] = true;
                $result['message'] = "Customer Summery Retrieves SuccessFully !!";
                $result['data'] = $custSummery;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data Not Found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";;
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function getCustomerLevelingReport($filterType, $filterData)
    {
        try {
            $_filterData = DigiPayUtil::parseFilterData($filterData);
            $customerLevelDetails = (new CustomerLevel())->getCustomerDetailsForLevel($filterType, $_filterData);
            if(!isset($customerLevelDetails) || empty($customerLevelDetails)){
                $error['status'] = false;
                $error['message'] = "Data Not Found";
                return response()->json($error)->setStatusCode(400);
            }

            $customerReport = [
                'bank_name' => null,
                'total_user' => null,
                'total_block' => null,
                'total_safe' => null,
            ];

            foreach ($customerLevelDetails as $_customerLevelDetails){

            }

        } catch (\Exception $ex) {
            $error['status'] = false;
            $error['message'] = "Internal Server Error";
            return response()->json($error)->setStatusCode(500);
        }
    }

    public function GetCustHidDetail($filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $CustHidDetail = (new CustHidDetails())->GetCustHidDetail($filterData, $limit, $pageNo);
            if(isset($CustHidDetail)) {
                $data = $CustHidDetail->items();
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['current_page'] = $CustHidDetail->currentPage();
                $result['last_page'] = $CustHidDetail->lastPage();
                $result['is_last_page'] = !$CustHidDetail->hasMorePages();
                $result['total_item'] = $CustHidDetail->total();
                $result['current_item_count'] = $CustHidDetail->count();
                $result['data'] = $data;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data  Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $error['status'] = false;
            $error['message'] = "Error while Get Cust Hid Details";
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function custBehaviour($filterData, $limit, $pageNo)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $CustHidDetail = (new Transactions())->getCustBehaviour($filterData, $limit, $pageNo);
            if(isset($CustHidDetail)) {
                $data = $CustHidDetail->items();
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['current_page'] = $CustHidDetail->currentPage();
                $result['last_page'] = $CustHidDetail->lastPage();
                $result['is_last_page'] = !$CustHidDetail->hasMorePages();
                $result['total_item'] = $CustHidDetail->total();
                $result['current_item_count'] = $CustHidDetail->count();
                $result['data'] = $data;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data  Not found";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::info($ex->getMessage());
            $error['status'] = false;
            $error['message'] = "Error while Get Cust Hid Details";
            return response()->json($error)->setStatusCode(400);
        }
    }

    public function getUserStateData($filterData)
    {
        try {
            $filterData = DigiPayUtil::parseFilterData($filterData);
            $userState=(new Transactions())->getUserStateData($filterData);
//            $l1=1; $l2=1; $l3=1;  $l4=1;
//            $count=0;
//            foreach ($userState as $key=> $state) {
//                $tempData=(new Transactions())->getstateCust($state->cust_state,$filterData);
//                foreach ($tempData as $_tempData) {
//                    $result = (new CustomerLevel())->getcustByDate($_tempData->customer_id, $filterData);
//                    if (isset($result) && !empty($result)){
//                        if ($result->user_security_level == 1) {
//                            $userState[$key]['Level1'] = $l1++;
//                        }
//                        if ($result->user_security_level == 2) {
//                            $userState[$key]['Level2'] = $l2++;
//                        }
//                        if ($result->user_security_level == 3) {
//                            $userState[$key]['Level3'] = $l3++;
//                        }
//                        if ($result->user_security_level == 4) {
//                            $userState[$key]['Level4'] = $l4++;
//                        }
//                      }
//                    $count++;
//                  }
//              }
            if(isset($userState)) {
                $result['status'] = true;
                $result['data'] = $userState;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data  Not found";
            return response()->json($error)->setStatusCode(400);

        }catch (\Exception $ex){
            Log::info($ex->getMessage());
            $error['status'] = false;
            $error['message'] = "Error while Get Customer State Data";
            return response()->json($error)->setStatusCode(400);
        }
    }
}

