<?php


namespace App\Auth;


use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\Store;

class CustomGuard implements Guard
{
    protected $session = false;

    public function __construct(CustomUserProvider $userProvider, Store $sessionStore)
    {
        $this->session = $sessionStore;
    }

    public function Login(CustomUserProvider $user){
        $this->session->put('user',  $user);
        $this->session->put('is_authenticated',  true);
        $this->session->save();
        return true;
    }

    public function user() : CustomUserProvider {
        if($this->session->exists('user')) {
            return $this->session->get('user');
        }

        return (new CustomUserProvider());
    }

    public function check() : bool {
        if($this->session->exists('is_authenticated')){
            if($this->session->get('is_authenticated') === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws AuthenticationException
     */
    public function authenticate() {
        if(!$this->check()) {
            return false;
        }
        return true;
    }


    /**
     * @throws AuthenticationException
     */
    public function logout() {
        $this->session->flush();
        return redirect()->route('login');
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest()
    {
        // TODO: Implement guest() method.
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return int|null
     */
    public function id()
    {
        // TODO: Implement id() method.
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        // TODO: Implement validate() method.
    }

    /**
     * Set the current user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @return void
     */
    public function setUser(Authenticatable $user)
    {
        // TODO: Implement setUser() method.
    }

    public function hasUser()
    {
        // TODO: Implement hasUser() method.
    }
}
