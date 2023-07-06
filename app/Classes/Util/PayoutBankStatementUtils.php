<?php

namespace App\Classes\Util;

use App\Models\Management\BankPerseUtr;
use App\Models\Management\BankStatement;
use App\Models\PaymentManual\PayoutBankStatementFile;
use App\Models\PaymentManual\PayoutManualRecon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
/**
 * @mixin Builder
 */
class PayoutBankStatementUtils
{
    public function getPayoutStatement($filterData, $limit, $pageNo)
    {
        try{
            $BankStatement = (new PayoutBankStatementFile())->getPayoutStatement($filterData, $limit, $pageNo);
            if(isset($BankStatement)) {
                $result = DigiPayUtil::withPaginate($BankStatement);
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Payout Statement Data Not found";
            return response()->json($error)->setStatusCode(400);
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }

    public function uploadStatementFile($fileName, $accountFile)
    {
        try {
            $fileName = DigiPayUtil::generateRandomNumber(10)."_".$fileName;
            $isExists = (new PayoutBankStatementFile())->checkBankFileExist($fileName);
            if($isExists){
                $result['status'] = false;
                $result['message'] = "File already exists, rename and upload";
                return response()->json($result)->setStatusCode(400);
            }

            $isUpload = (new AWSUtils())->uploadPayoutBankFiles($fileName, $accountFile);
            if($isUpload){
                if((new PayoutBankStatementFile())->uploadStatementFile($fileName)) {
                    $result['status'] = true;
                    $result['message'] = "File Uploaded";
                    return response()->json($result)->setStatusCode(200);
                }
            }

            $error['status'] = false;
            $error['message'] = "Error while upload file";
            return response()->json($error)->setStatusCode(400);
        } catch (\Exception $ex) {
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }
    public function showAddedUtr($fileName)
    {
        try{
            $addedUtr = (new PayoutManualRecon())->showAddedUtr($fileName);
            if(isset($addedUtr)) {
                $result['status'] = true;
                $result['message'] = 'Data Retrieve successfully';
                $result['data'] = $addedUtr;
                return response()->json($result)->setStatusCode(200);
            }
            $error['status'] = false;
            $error['message'] = "Data Not found";
            return response()->json($error)->setStatusCode(400);
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false,'message' => 'Internal Server Error'])->setStatusCode(500);
        }
    }
}
