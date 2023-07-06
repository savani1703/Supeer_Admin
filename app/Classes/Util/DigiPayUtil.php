<?php


namespace App\Classes\Util;


use App\Models\Management\MerchantDetails;
use App\Models\Management\Payout;
use App\Models\Management\PgRouter;
use App\Models\Management\Transactions;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DigiPayUtil
{

    const PRIVATE_KEY = 'EOy-xNhDZmrdoLOJnkwJXrkrzb7i9Bu8VofNygzoXOM=';

    public static function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function getClientIp() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    static public function generateMerchantId(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 14; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return 'MID_'.$randomString;
    }

    static public function createJwtToken($requestData) {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];
        $payload = [
            'iss'           => 'DIGIPAYZONE',
            'timestamp'     => Carbon::now()->unix(),
        ];
        $base64UrlData  =  (new DigiPayUtil())->base64UrlEncode(json_encode($requestData));
        $base64UrlHeader =  (new DigiPayUtil())->base64UrlEncode(json_encode($header));
        $base64UrlPayload =  (new DigiPayUtil())->base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $base64UrlHeader . "." .$base64UrlData . "." . $base64UrlPayload, DigiPayUtil::PRIVATE_KEY, true);
        $base64UrlSignature = (new DigiPayUtil())->base64UrlEncode($signature);
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public function base64UrlEncode($data)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($data)
        );
    }

    public static function generatePayoutMetaId($last_account_id = "PN000", $prefix = "PN") {
        $a = substr($last_account_id, -3);
        $b = $a + 0001;
        $c = $prefix;
        for ($i = 0; $i < (strlen($a) - strlen($b)); $i++) {
            $c .= "0";
        }
        $c .= $b;
        return $c;
    }

    public static function generateAvailableBankId($lastAvailableBankId)
    {
        $newAvailableBankId = "ab_00001";
        if(isset($lastAvailableBankId)) {
            $a = substr($lastAvailableBankId, -1 * abs(strlen(str_replace("ab_", "", $lastAvailableBankId))));
            $b = abs($a + 1);
            $strLength = (strlen($a) - strlen($b)) < 0 ? abs((strlen($a) - strlen($b))) : (strlen($a) - strlen($b));
            $newAvailableBankId = "ab_";
            for ($i = 0; $i < $strLength; $i++) {
                $newAvailableBankId .= "0";
            }
            $newAvailableBankId .= $b;

            return $newAvailableBankId;
        }
        return $newAvailableBankId;
    }

    public static function generateRandomNumber($length = 18): string
    {
        $characters = '123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function TO_IST($utcDate, $format = "Y-m-d H:i:s") {
        if(isset($utcDate) && !empty($utcDate)) {
            return Carbon::parse($utcDate, "UTC")->setTimezone("Asia/Kolkata")->format($format);
        }
        return "";
    }

    public static function TO_UTC($utcDate, $format = "Y-m-d H:i:s") {
        if(isset($utcDate) && !empty($utcDate)) {
            return Carbon::parse($utcDate, "Asia/Kolkata")->setTimezone("UTC")->format($format);
        }
        return "";
    }
    public static function TO_IST_DATE($utcDate, $format = "Y-m-d") {
        if(isset($utcDate) && !empty($utcDate)) {
            return Carbon::parse($utcDate)->format($format);
        }
        return "";
    }

    static function parseFilterData($filterData) {
        if(isset($filterData)) {
            if(isset($filterData['start_date']) && !empty($filterData['start_date'])) {
                $filterData['start_date'] = DigiPayUtil::TO_UTC($filterData['start_date']);
            }
            if(isset($filterData['end_date']) && !empty($filterData['end_date'])) {
                $filterData['end_date'] = DigiPayUtil::TO_UTC($filterData['end_date']);
            }
        }
        return $filterData;
    }

    static function withPaginate($data) {
        $result = [];
        if(isset($data)) {
            $result['status'] = true;
            $result['message'] = 'Data Retrieve successfully';
            $result['current_page'] = $data->currentPage();
            $result['last_page'] = $data->lastPage();
            $result['is_last_page'] = !$data->hasMorePages();
            $result['total_item'] = $data->total();
            $result['current_item_count'] = $data->count();
            $result['data'] = $data->items();
        }
        return $result;
    }

    public static function multiSearch(array $array, array $pairs)
    {
        foreach ($array as $aKey => $aVal) {
            $coincidences = 0;
            foreach ($pairs as $pKey => $pVal) {
                if (array_key_exists($pKey, $aVal) && $aVal[$pKey] == $pVal) {
                    $coincidences++;
                }
            }
            if ($coincidences == count($pairs)) {
                return $aKey;
            }
        }

        return -1;
    }

    public static function generatePaymentId()
    {
        $txn = self::generateRandomNumber(11);
        if (!(new Transactions())->checkTransactioIsExist($txn)) {
            return $txn;
        }
        return self::generatePaymentId();
    }

    static public function calculateFees($amount, $fees)
    {
        $amount = floatval($amount);
        $fees = floatval($fees);
        return ($amount * $fees) / 100;
    }

    public static function getAuthUser()
    {
        if(Auth::check()) {
            return Auth::user()->email_id;
        }
        return  "SYSTEM_USER";
    }

    public static function getAuthUserRoleId()
    {
        if(Auth::check()) {
            return Auth::user()->role_id;
        }
        return 0;
    }

    public static function PayInPgList($pgType = "all") {
        try {
            return (new PgRouter())->getPayInPgList($pgType);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function PayoutPgList($pgType = "all") {
        try {
            return (new PgRouter())->getPayoutPgList($pgType);
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function MerchantList() {
        try {
            return (new MerchantDetails())->getMerchantList();
        } catch (\Exception $ex) {
            return null;
        }
    }

    public static function inArrayRecursiv($needle, $haystack, $strict = false) {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::inArrayRecursiv($needle, $item, $strict))) {
                return true;
            }
        }
        return false;
    }

    public static function generateTempUtr($prFix)
    {

        $tempUtr = "IDFB".$prFix.self::generateRandomNumber(10);
        if (!(new Payout())->checkTempUtrIsExist($tempUtr)) {
            return $tempUtr;
        }
        return self::generateTempUtr($prFix);
    }
}
