<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use Tymon\JWTAuth\Facades\JWTAuth;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $rendered = parent::render($request, $exception);
        $code = $rendered->getStatusCode();
        $message = $code == 404 || $code == 405 ? 'Endpoint tidak ditemukan' : $exception->getMessage();
        try {
            JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $th) {
            return $this->sendResponse('Token Expired', 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $th) {
            return $this->sendResponse('Token Invalid', 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $th) {
            return $this->sendResponse('Token not provided', 401);
        }
        return $this->sendResponse($message, $code);
    }
    public function sendResponse($message = null, $code)
    {
        $status = $code == 200 ? true : false;
        return response()->json([
            'status' => $status,
            'message' => $message
        ], $code);
    }
}
