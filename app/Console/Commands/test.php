<?php

namespace App\Console\Commands;

use App\Classes\Util\SupportUtils;
use App\Models\Management\CustomerLevel;
use App\Models\Management\MerchantPaymentMeta;
use App\Classes\Util\TransactionUtils;
use App\Models\Management\Transactions;
use App\Models\PaymentManual\AvailableBank;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      /*  $startDate = \Illuminate\Support\Carbon::parse("2023-04-23", "Asia/Kolkata")->setTimezone("UTC")->format("Y-m-d H:i:s");
        $endDate = \Illuminate\Support\Carbon::parse("2023-04-23", "Asia/Kolkata")->addDay()->subSecond()->setTimezone("UTC")->format("Y-m-d H:i:s");
        dd($startDate,$endDate);*/
        Cache::flush();
    }
}
