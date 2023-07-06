<?php

namespace App\Classes\BankFileCompiler\Payout;

use App\Models\Management\Payout;
use App\Models\PaymentManual\PayoutBankStatementFile;
use Illuminate\Support\Facades\Log;

class YesBankStatementParser
{
    public function statementParse($id, $fileName, $transactionArray, $accountNumber)
    {
        try {
            $totalTransaction = count($transactionArray[0]);
            if($totalTransaction > 1){
                (new PayoutBankStatementFile())->addTotalCount($id, $totalTransaction);
            }

            foreach ($transactionArray[0] as $key => $txn) {
                (new PayoutBankStatementFile())->addProgressCount($id, $key + 1);
                if(isset($txn[7]) && !empty($txn[7]) && isset($txn[8]) && !empty($txn[8]) && isset($txn[9]) && !empty($txn[9])){
                    $payoutId           = $txn[7];
                    $referenceNumber    = $txn[8];
                    $status             = $txn[9];
                    $checkPayout = (new Payout())->checkPayoutEligibleForYes($payoutId);
                    if($checkPayout){
                        if(strcmp($status,'Success') === 0 && strlen($referenceNumber) === 12){
                            (new Payout())->markPayoutAsSuccssForYes($payoutId, $referenceNumber);
                            echo "\n Mark As Success Payout Id Is : ".  $payoutId;
                        }
                        if(strcmp($status,'Rejected/Failed at beneficiary bank') === 0){
                            (new Payout())->markPayoutAsFailedForYes($payoutId, $status);
                            echo "\n Mark As Failed Payout Id Is : ".  $payoutId;
                        }
                    }else{
                        echo "\n Payout Not Eligible Payout Id : ". $payoutId;
                    }
                }
                usleep(10);
            }
            (new PayoutBankStatementFile())->totalAddedUtr($id, 0);
        }catch (\Exception $ex){
            Log::error('Error while executing SQL Query', [
                'class' => __CLASS__,
                'function' => __METHOD__,
                'file' => $ex->getFile(),
                'line_no' => $ex->getLine(),
                'error_message' => $ex->getMessage(),
            ]);
            (new PayoutBankStatementFile())->markAsError($id);
            (new PayoutBankStatementFile())->setRemark($id, $ex->getMessage());
        }
    }
}
