<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetUserPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SetUserPassword';

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
       $password= Str::random(20);
       echo "\n".$password;
        DB::connection('merchant_management')->table('tbl_support_user')->where('email_id','paresh@gmail.com')->update([
           'password_new'=>Hash::make($password)
        ]);
    }
}
