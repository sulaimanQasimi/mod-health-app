<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
        $this->middleware('throttle:password-reset')->only('sendResetLinkEmail');
    }

    /**
     * Log password-reset requests for security monitoring (no secrets).
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        Log::info('Password reset link requested', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
        ]);

        return back()->with('status', trans($response));
    }
}
