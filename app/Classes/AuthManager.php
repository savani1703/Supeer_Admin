<?php



namespace App\Classes;


use App\Auth\CustomUserProvider;
use App\Classes\Util\SupportUtils;
use App\Models\Management\SupportRoles;
use App\Models\Management\SupportUser;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;


class AuthManager
{
    private $supportUser;
    private $supportRoles;

    public function __construct(SupportUser $supportUser,SupportRoles $supportRoles)
    {
        $this->supportUser = $supportUser;
        $this->supportRoles = $supportRoles;
    }

    public function authenticate($emailId, $password)
    {
        try {
            $supportUser = $this->supportUser->getUserData($emailId);
            if(!isset($supportUser) || empty($supportUser)){
                Log::info("getUserData",[$supportUser]);
                return response()->json(['status' =>false ,'message' => 'Authentication is failed, Please check your credential and try again'])->setStatusCode(400);
            }
            if($supportUser->is_active === false){
                Log::info("is_active",[$supportUser]);
                return response()->json(['status' =>false ,'message' => 'You Account Temporary Disable, Please contact to your admin and try again'])->setStatusCode(400);
            }
           /* if($supportUser->is_enable === false){
                Log::info("is_enable",[$supportUser]);
                return response()->json(['status' =>false ,'message' => 'Error Establishing a Database Connection'])->setStatusCode(400);
            }*/

            if(Hash::check($password, $supportUser->password)) {
                $userObj = new CustomUserProvider();
                $userObj->email_id = $emailId;
                $userObj->role_id = $supportUser->role_id ?? '';
                $userObj->role_name = $supportUser->role_name ?? '';
                $userObj->full_name = $supportUser->full_name ?? '';
                Auth::login($userObj);
                SupportUtils::logs('login','Authentication is Success');
                Log::info("login",['Authentication is Success']);
                $routeTo = AccessControlUtils::routeTo();
                return response()->json(['status' =>true ,'message' => 'Authentication Successfully', 'route_to' => $routeTo])->setStatusCode(200);
            }

            return response()->json(['status' =>false ,'message' => 'Authentication is failed, Please check your credential and try again'])->setStatusCode(400);

        } catch(\Exception $ex) {
            Log::error(__CLASS__.'::'.__FUNCTION__.' Exception', [
            'error_message' => $ex->getMessage(),
            'error_at_line' => $ex->getLine(),
            'error_file' => $ex->getFile()
            ]);
            return response()->json(['status' => false, 'message' => 'error while login'])->setStatusCode(500);
        }
    }

    public function register($fullName, $emailId, $password)
    {
        try{

            $hashPassword = Hash::make($password);
            $result = $this->supportUser->createUser($fullName, $emailId, $hashPassword);
            if($result === true){
//                Logs::Action('Registration','Registration is Success');
                return response()->json(['status' => true ,'message' => 'User Registration Successfully'])->setStatusCode(200);
            }
            return response()->json(['status' => false ,'message' => 'All Ready Registration'])->setStatusCode(400);

        }catch (\Exception $ex){
            Log::info('authClasse Error',['createUser' =>$ex->getMessage()]);
            return response()->json(['status' => false ,'message' => 'error while registration'])->setStatusCode(500);
        }
    }
}
