<?php

namespace App\Exceptions;

use App\Traits\Helpers\ExceptionTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    use ExceptionTrait;

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson() || $request->wantsJson() || $request->isJson() || $request->is('api/*'))
        {
            return $this->apiExceptions($request,$exception);
        }
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return response()->json([
                'status' => false,
                'message' => 'Action non authoriser.',
            ], 403);
        }

        return parent::render($request, $exception);
    }
}
