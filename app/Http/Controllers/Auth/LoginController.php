<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['status'] = 1;

        return $credentials;
    }

    /**
     * Always send users to the login page after logout (avoid hitting auth/role
     * middleware on "/" which can surface as a 403 for guests).
     */
    protected function loggedOut(Request $request)
    {
        return redirect()->route('login');
    }
}
