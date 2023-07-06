<?php

namespace App\Common;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DateHelper
{
    public static function diffForHumans($date): ?string
    {
        try {
            return Carbon::parse($date)->diffForHumans();
        }catch (\Exception $ex){
            Log::error(__CLASS__.'::'.__FUNCTION__.' Query Exception', [
                'error_message' => $ex->getMessage(),
                'error_at_line' => $ex->getLine(),
                'error_file' => $ex->getFile()
            ]);
            return null;
        }
    }
}
