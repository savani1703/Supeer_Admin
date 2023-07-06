<?php


namespace App\Models\Management;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class SupportRoles extends Model
{
    protected $table = 'tbl_support_roles';
    public $timestamps = false;
    protected $primaryKey = 'role_id';
    public $incrementing = false;

    public function getUserRolesById($roleId)
    {
        try {
            $result = $this->where('role_id',$roleId)->first();
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            return null;
        }
    }

}
