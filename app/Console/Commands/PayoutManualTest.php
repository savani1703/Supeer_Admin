<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PayoutManualTest extends Command
{

    protected $signature = 'PayoutManual';
    protected $description = 'Command description';
    public function handle()
    {
        try {

        }catch (\Exception $ex){
            dd($ex->getMessage());
        }
    }
}
