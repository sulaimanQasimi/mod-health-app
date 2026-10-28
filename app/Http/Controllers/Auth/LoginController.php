<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        sendLockoutResponse as protected traitSendLockoutResponse;
    }

    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Max failed attempts before lockout.
     */
    protected $maxAttempts = 5;

    /**
     * Lockout decay in minutes.
     */
    protected $decayMinutes = 1;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('throttle:login')->only('login');
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

    /**
     * Log failed authentication attempts for security monitoring.
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        Log::warning('Failed login attempt', [
            'username' => $request->input($this->username()),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * Log when an account is temporarily locked out.
     */
    protected function sendLockoutResponse(Request $request)
    {
        Log::notice('Login lockout triggered', [
            'username' => $request->input($this->username()),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'seconds' => $this->limiter()->availableIn($this->throttleKey($request)),
        ]);

        return $this->traitSendLockoutResponse($request);
    }
}
