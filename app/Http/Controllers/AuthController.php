<?php

namespace App\Http\Controllers;

use App\Classes\AuthManager;
use App\Classes\Util\SupportUtils;
use App\Plugin\AccessControl\AccessControl;
use App\Plugin\AccessControl\Utils\AccessControlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Traits\Encryptable;

class AuthController extends Controller
{
    private $authManager;
    use Encryptable;

    public function __construct(AuthManager $authManager){
        $this->authManager = $authManager;
    }
    public function authenticate(Request  $request){

        $validator = Validator::make($request->all(),[
            'email_id'  =>'required|email',
            'password'  =>'required'
        ],[
            'email.required'    => 'email id is Required',
            'email.email'       => 'email id is not valid',
            'password.required' => 'password is required',
        ]);
        if ($validator->fails()){
            $error = $validator->errors()->first();
            return response()->json(['status'=> false,'message'=> $error])->setStatusCode(400);
        }
        return  $this->authManager->authenticate($request->email_id, $request->password);
    }

    public function register(Request  $request){
        $validator = Validator::make($request->all(),[
            'full_name'  =>'required',
            'email_id'  =>'required|email',
            'password'  =>'required',
        ],[
            'full_name.required'=> 'full name is Required',
            'email.required'    => 'email id is Required',
            'email.email'       => 'email id is not valid',
            'password.required' => 'password is required',
        ]);
        if ($validator->fails()){
            $error = $validator->errors()->first();
            return response()->json(['status'=> false,'message'=> $error])->setStatusCode(400);
        }
        return  $this->authManager->register($request->full_name, $request->email_id, $request->password);
    }

    public function login()
    {

        if(Auth::check()){
            return redirect('dashboard');
        }
        return view('login');
    }

    public function dashboard()
    {
        if(Auth::check()){
            return redirect(AccessControlUtils::routeTo());
        }
        return redirect("login");
    }

    public function logout()
    {
        SupportUtils::logs('Logout','User Logout');
        (new AccessControl())->destroySession();
        Auth::logout();
        return redirect("login");
    }
}
