<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
    \Illuminate\Auth\AuthenticationException::class,
    \Illuminate\Auth\Access\AuthorizationException::class,
    \Symfony\Component\HttpKernel\Exception\HttpException::class,
    \Illuminate\Database\Eloquent\ModelNotFoundException::class,
    \Illuminate\Session\TokenMismatchException::class,
    \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->expectsJson()) {

            //Authorization
            if ($exception instanceof AuthorizationException)
            {
                return response()->json([
                    'data' =>   [ 'error' => 'You are prohibited!' ]
                    ],
                    403);
            }


            //Model not found
            if ($exception instanceof ModelNotFoundException)
            {
                $modelClass = end(explode('\\', $exception->getModel()));
                return response()->json([
                    'data' => ['error' => $modelClass . ' not found']
                    ],
                    404);
            }


            //Not found exception
            if ($exception instanceof NotFoundHttpException)
            {
                return response()->json([
                    'data' =>   [ 'error' => 'Model is null' ]
                    ],
                    404);
            }

            //Get null result on requesting Model instance
            // if ($exception instanceof FatalThrowableError)
            // {
            //     return response()->json([
            //         'data' =>   [ 'error' => 'Not found' ]
            //         ],
            //         404);
            // }
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'data' =>   [
                'error' => 'Please login to continue!'
                ]
                ],
                401);
        }

        return redirect()->guest(route('login'));
    }
}