<?php

namespace App\Classes\Util;

class ManualPayoutUtils
{

    public function getYesBankMappingDetails($file_data)
    {
        try {

            $payoutDetails = json_decode($file_data,false);
            $collection = collect();
            if(isset($payoutDetails) && !empty($payoutDetails)){
                foreach ($payoutDetails as $key => $_payoutDetails) {
                    $collection[] = array(
                        'SRNO'                  => $key + 1,
                        'BENENAME'              => $_payoutDetails->bank_holder ? $_payoutDetails->bank_holder : 'N/A',
                        'IMPS'                  => 'IMPS',
                        'ACCOUNT NUMBER'        => $_payoutDetails->to_account_number ?  intval($_payoutDetails->to_account_number) : 'N/A',
                        'AMOUNT'                => $_payoutDetails->payout_amount ? $_payoutDetails->payout_amount : 'N/A',
                        'IFSC CODE'             => $_payoutDetails->ifsc ? $_payoutDetails->ifsc : 'N/A',
                        '91BENEMOBILE NUMBER'   => null,
                        'DESCRIPTION'           => $_payoutDetails->payout_id ? $_payoutDetails->payout_id : 'N/A',
                    );
                }
            }
            if ($collection) {
                return $collection;
            }
            return null;
        }catch (\Exception $ex){
            return null;
        }
    }
}
