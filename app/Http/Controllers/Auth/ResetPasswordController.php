<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:password-reset')->only('reset');
    }

    /**
     * Log successful password resets for security monitoring.
     */
    protected function sendResetResponse(Request $request, $response)
    {
        Log::notice('Password reset completed', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        return redirect($this->redirectPath())
            ->with('status', trans($response));
    }
}
