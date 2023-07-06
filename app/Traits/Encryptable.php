<?php
/**
 * Created by PhpStorm.
 * User: vipul
 * Date: 18-05-2020
 * Time: 21:32
 */
namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Encryptable
{
    public function getAttribute($key)
    {
        try {
            $value = parent::getAttribute($key);
            if (in_array($key, $this->encryptable)) {
                $value = $this->decryptedAttribute($value);
                return $value;
            }
            return $value;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public function setAttribute($key, $value)
    {
        try {
            if (in_array($key, $this->encryptable)) {
                $value = $this->encryptedAttribute($value);
            }
            return parent::setAttribute($key, $value);
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public function encryptedAttribute($value)
    {
        try {
            $ky      = $this->getEncryptionKey();
            $key     = html_entity_decode($ky);
            $encrypt = openssl_encrypt ( $value , config('app.cipher') , $key, 0, config('app.iv'));
            return $encrypt;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    public function decryptedAttribute($value)
    {
        try {
            $ky      = $this->getEncryptionKey();
            $key     = html_entity_decode($ky);
            $decrypted = openssl_decrypt ( $value , config('app.cipher') , $key, 0, config('app.iv'));
            return $decrypted;
        } catch (\Exception $ex) {
            Log::error($ex->getMessage());
        }
    }

    private function getEncryptionKey() {
        $masterKey = '$10$95321&%!S!ssss%==';
        $secret = config('app.enc_key');
        $key = md5($masterKey.$secret);
        return $key;
    }
}
