<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class testModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testModule';

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
        $dt="App\Models\PaymentAuto\NuPay\NuPayPayoutMeta";
       dd((new $dt)->first());
    }
}
