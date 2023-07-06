<?php

namespace App\Console\Commands;

use App\Models\Management\Transactions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterSupportUser extends Command
{

    protected $signature = 'RegisterSupportUser';

    protected $description = 'Command description';


    public function handle()
    {
        $filterData = [
            "payment_method" => "ALL",
            "status" => "Success",
            "meta_id" => "ALL",
            "start_date" => "2022-07-01 00:00:00",
            "end_date" => "2022-07-31 23:59:59",
            "pg_type" => "ALL",
        ];
       // $data = (new Transactions())->getTransactionDetailsForReport($filterData,true);
        $emailId        = "test@gmail.com";
        $name           = "test";
        $plainPassword  = Str::random(20);
        $password       = Hash::make($plainPassword);
        $res = DB::connection("merchant_management")
                ->table("tbl_support_user")
                ->insert([
                    "email_id" => $emailId,
                    "password" => $password,
                    "full_name" => $name,
                    "role_id" => 1,
                    "is_active" => 1,
                ]);

        dd(
            "User Created $res",
            "Name:  $name",
            "Email:  $emailId",
            "Password:  $plainPassword",
        );

        return 0;
    }
}
