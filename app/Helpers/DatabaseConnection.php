<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DatabaseConnection
{
    public static function setConnection()
    {
        $host='65.0.154.4';
        $username='sammy';
        $password='nIwgGFy72xeC0VA28qWYN';

        config(['database.connections.merchant_management.host' =>$host]);
        config(['database.connections.merchant_management.username' =>$username]);
        config(['database.connections.merchant_management.password' =>$password]);

   /*     config(['database.connections.merchant_management' => [
            'driver' => 'mysql',
            'host' => $host,
            'username' => $username,
            'password' => $password
        ]]);
        config(['database.connections.payment_auto' => [
            'driver' => 'mysql',
            'host' => $host,
            'username' => $username,
            'password' => $password
        ]]);
        config(['database.connections.payment_manual' => [
            'driver' => 'mysql',
            'host' => $host,
            'username' => $username,
            'password' => $password
        ]]);*/

    }
}
