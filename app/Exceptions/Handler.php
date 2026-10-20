<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // After logout (or expired session), role/permission middleware throws 403 —
        // send guests to login instead of showing Forbidden.
        $this->renderable(function (UnauthorizedException $e, $request) {
            if (! auth()->check()) {
                return redirect()->guest(route('login'));
            }
        });
    }
}
