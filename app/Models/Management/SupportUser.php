<?php

namespace App\Models\Management;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SupportUser extends Model
{

    protected $table = 'tbl_support_user';
    public $timestamps = false;
    protected $primaryKey = 'role_id';
    public $incrementing = false;
    protected $casts=[
        "is_active"=>"boolean",
        "is_enable"=>"boolean"
    ];

    public function getUserData($email)
    {
        try{
            $result = $this->where('email_id',$email)->first();
            if($result){
                return $result;
            }
            return null;
        }catch (QueryException $ex){
            Log::info('error while get User Data'.$ex->getMessage());
            return null;
        }
    }

    public static function hasRoles($role_names){
        if (Auth::check())
        {
            $email_id = Session::get('user')->email_id;
            $role_names = explode('|', $role_names);
            // create array to store multiple roles to check

            $check_role = SupportUser::select('tbl_support_roles.role_name')
                ->join('tbl_support_roles', 'tbl_support_roles.role_id', '=', 'tbl_support_user.role_id')
                ->where('tbl_support_user.email_id', $email_id)
                ->where(function ($query) use ($role_names)
                {
                    $query->orWhereIn('tbl_support_roles.role_name', $role_names);
                })
                ->first();
            return $check_role;
        }
        return false;
    }

    public function createUser($fullName, $emailId, $hashPassword){
        try{
            $user = $this->where('email_id',$emailId)->exists();
            if($user === true){
                return false;
            }else{
                $this->full_name    = $fullName;
                $this->email_id     = $emailId;
                $this->password     = $hashPassword;
                $this->is_active    = true;

                if($this->save()){
                    return true;
                }
            }
            return false;
        }catch (QueryException $ex){
            Log::critical('UserModels Error',['CreateUser' => $ex->getMessage()]);
            Log::error('UserModels Error',['CreateUser' =>$ex->getMessage()]);
            return false;
        }
    }

}
