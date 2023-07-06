<?php

namespace App\Plugin\AccessControl;

use App\Classes\Util\DigiPayUtil;
use App\Models\Support\RoleAccessModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AccessControl
{
    public function hasAccessRoute($route) {
        $sessionId = md5(DigiPayUtil::getAuthUser());
        $accessData = null;
        if(Cache::has($sessionId)) {
            $accessData = Cache::get($sessionId);
        } else {
            $this->storeSession(DigiPayUtil::getAuthUserRoleId());
            $accessData = Cache::get($sessionId);
        }
        if(isset($accessData)) {
            if(
                $accessData->where("module_route", "=", $route)->count() > 0 ||
                $accessData->where("child_module_route", $route)->count() > 0
            ) {
                return true;
            }
        }
        return false;
    }

    public function hasAccessModule($moduleId) {
        $sessionId = md5(DigiPayUtil::getAuthUser());
        $accessData = null;
        if(Cache::has($sessionId)) {
            $accessData = Cache::get($sessionId);
        } else {
            $this->storeSession(DigiPayUtil::getAuthUserRoleId());
            $accessData = Cache::get($sessionId);
        }
        if(isset($accessData)) {
            if($accessData->where("module_id", "=", $moduleId)->count() > 0) {
                return true;
            }
        }
        return false;
    }

    public function storeSession($roleId) {
        $roleData = (new RoleAccessModule())->getByRoleId($roleId);
        $parsedRoleData = [];
        if(isset($roleData)) {
            foreach ($roleData as $_roleData) {
                if(isset($_roleData->supportModule)) {
                    $parsedRoleData[] = collect($_roleData->supportModule->toArray());
                }
            }
        }

        if(sizeof($parsedRoleData) > 0) {
            $parsedRoleData = new Collection($parsedRoleData);
            $sessionId = md5(DigiPayUtil::getAuthUser());
            Cache::put($sessionId, $parsedRoleData, 600);
            return Cache::has($sessionId);
        }
        return false;
    }

    public function destroySession() {
        $sessionId = md5(DigiPayUtil::getAuthUser());
        $sidebarSessionId = md5(DigiPayUtil::getAuthUser()."siderbar");
        if(Cache::has($sessionId)) {
            return Cache::forget($sessionId);
        }
        if(Cache::has($sidebarSessionId)) {
            return Cache::forget($sidebarSessionId);
        }
        return false;
    }
}
