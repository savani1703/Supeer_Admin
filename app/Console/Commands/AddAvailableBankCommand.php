<?php

namespace App\Console\Commands;

use App\Classes\Util\DigiPayUtil;
use App\Classes\Util\ReportType;
use App\Classes\Util\ReportUtils;
use App\Classes\Util\SupportUtils;
use App\Models\Management\MerchantDetails;
use App\Models\Management\PgRouter;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;

class AddAvailableBankCommand extends Command
{

    protected $signature = 'AddAvailableBankCommand';
    protected $description = 'AddAvailableBankCommand';

    public function handle()
    {
        dd((new MerchantDetails())->getMerchantList());
//        $payload = [
//            'start_date' => "2022-07-20 18:30:00",
//            'end_date' => "2022-07-21 18:29:59",
//            'status' => "Success",
//        ];
        $payload = [
            'transaction_id' => "41258972717",
            'txn_token' => "t4c9d13f2-0644-46d9-bd74-e7973868267a",
            'merchant_id' => "MID_8WUBWQL6I4KJZT",
        ];

//        dd((new SupportUtils())->generateReport($payload, ReportType::PAYOUT));

        dd(DigiPayUtil::createJwtToken($payload));
//        $filePath = "C:\Users\pnbha\Downloads\upi_bank_meta.xlsx";
//        $import = (new FastExcel())->import($filePath, function ($data) {
//            return [
//                "account_holder_name" => $data['label'],
//                "upi_id" => $data['upi'],
//                "vendor_id" => "-",
//                "vendor_name" => "-",
//                "bank_name" => $data['bank'],
//                "account_number" => strval($data['account']),
//                "ifsc_code" => strval($data['ifsc']),
//            ];
//        })->toArray();
//
//        if(isset($import) && sizeof($import) > 0) {
//            foreach ($import as $bankData) {
//                $pgModule = "App\Models\PaymentManual\AvailableBank";
//                if(isset($pgModule)) {
//                    try {
//                        (new $pgModule)->validateFormData($bankData);
//                    } catch (\Exception $e) {
//                        echo "\n".$e->getMessage()."\n";
//                    }
//                    $accountId = (new $pgModule)->getAccountId();
//                    if((new $pgModule)->addMeta($bankData, $accountId)) {
//                        echo "\n Bank Added ".$bankData['account_holder_name']."\n";
//                    } else {
//                        echo "\n Bank Add Failed ".$bankData['account_holder_name']."\n";
//                    }
//                }
////                dd("x");
//            }
//        }


    }
}
