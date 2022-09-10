<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
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

    /**
     * @param $request
     * @param  \Throwable  $e
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     * @throws \Throwable
     * @Author: Roy
     * @DateTime: 2022/9/10 下午 02:56
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'code'    => 500,
                'message' => 'Server Errors',
                'details' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }

        return parent::render($request, $e);
    }
}
