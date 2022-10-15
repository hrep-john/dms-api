<?php

namespace App\Exceptions;

use App;
use App\Helpers\ApiErrorResponse;
use App\Mail\ExceptionOccured;
use App\Mail\PasswordResetOtp;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Mail;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
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
        // Override Spatie's UnauthorizedException message
        $this->renderable(function (UnauthorizedException $e, $request) {
            return response()->json([
                'message'  => $e->getMessage(),
                'errorCode' => ApiErrorResponse::UNAUTHORIZED_CODE,
                'errors' => NULL
            ], 403);
        });

        $this->reportable(function (Throwable $e) {
            Logger('reportable');
            Logger(JSON_ENCODE($e));

            if (App::environment('production')) {
                $this->sendEmail($e);
            }
        });
    }

    /**
     * Write code on Method
     *
     * @return response()
     */
    public function sendEmail(Throwable $exception)
    {
        try {
            $content['message'] = $exception->getMessage();
            $content['file'] = $exception->getFile();
            $content['line'] = $exception->getLine();
            $content['trace'] = $exception->getTrace();
            $content['url'] = request()->url();
            $content['body'] = request()->all();
            $content['ip'] = request()->ip();

            $email = env('SUPERADMIN_EMAIL', 'hrep.john@gmail.com');

            Mail::to($email)->send(new ExceptionOccured($content));
        } catch (Throwable $exception) {
            Log::error($exception);
        }
    }

    /**
     * Convert a validation exception into a JSON response.
     *
     * @param Request $request
     * @param ValidationException $exception
     * @return JsonResponse
     */
    protected function invalidJson($request, ValidationException $exception)
    {
        return response()->json([
            'errorCode' => ApiErrorResponse::VALIDATION_ERROR_CODE,
            'message' => 'A validation error has occurred.',
            'errors' => $this->transformErrors($exception),
        ], $exception->status);
    }

    // Transform the error messages,
    private function transformErrors(ValidationException $exception)
    {
        $errors = (object) [];
        foreach ($exception->errors() as $field => $message) {
            $errors->{$field} = $message;
        }
        return $errors;
    }

    /**
     * Modify the unauthenticated response.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return response()->json(
                ['message' => $exception->getMessage(), 'errorCode' => ApiErrorResponse::UNAUTHENTICATED_CODE, 'errors' => NULL],
                401);
    }


    /**
     * Modify the Route not found response.
     *
     * @param Request $request
     * @param Throwable $e
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $e)
    {
        if ($e instanceof NotFoundHttpException || $e instanceof MethodNotAllowedHttpException) {
            return response()->json(
                ['message' => 'Route not found.', 'errorCode' => ApiErrorResponse::UNKNOWN_ROUTE_CODE, 'errors' => NULL],
                404);
        } else if ($e instanceof ThrottleRequestsException) {
            Log::alert('HTTP rate limit reached.');
            return response()->json(
                ['message' => 'Too many requests.', 'errorCode' => ApiErrorResponse::TOO_MANY_REQUESTS_CODE, 'errors' => NULL],
                429);
        }
        return parent::render($request, $e);
    }
}
