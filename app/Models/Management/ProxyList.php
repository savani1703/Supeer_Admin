<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class ProxyList extends Model
{
    protected $connection = 'merchant_management';
    protected $table = 'tbl_proxy_list';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    public function getProxyList()
    {
        try {
            $data = $this->orderBy("label_name", "asc")->get();
            if($data->count() > 0) {
                return $data;
            }
            return null;
        } catch (QueryException $ex) {
            report($ex);
            return null;
        }
    }

    public function checkIsExists($label, $proxyIp)
    {
        try {
            return $this->where("label_name", $label)->where("ip_proxy", $proxyIp)->exists();
        } catch (QueryException $ex) {
            report($ex);
            return true;
        }
    }

    public function addProxy($label, $proxyIp)
    {
        try {
            $this->label_name = $label;
            $this->ip_proxy = $proxyIp;
            if($this->save()) {
                return true;
            }
            return false;
        } catch (QueryException $ex) {
            report($ex);
            return false;
        }
    }

}
