<?php

namespace App\Traits;

/*
|--------------------------------------------------------------------------
| Api Responder Trait
|--------------------------------------------------------------------------
|
| This trait will be used by controllers for any response sent to clients.
|
*/

use App\Helpers\ApiErrorResponse;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

trait ApiResponder
{
    /**
     * Return a success JSON response.
     *
     * @param $data
     * @param int $statusCode
     * @param array $headers
     * @return Response
     */
    protected function success($data, int $statusCode, array $headers = [])
    {
        return response($data, $statusCode, $headers);
    }

    /**
     * Return an error JSON response.
     *
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @param string|null $errorCode
     * @throws HttpResponseException
     *
     * @return void
     */
    protected function throwError(
        string $message, 
        ?array $errors, 
        int $statusCode, 
        string $errorCode = NULL, 
        string $errorMessage = NULL
    ){
        Logger('apiresponsder throwError');
        Logger([
            'message' => $message,
            'errors' => $errors,
            'error_message' => $errorMessage,
            'status_code' => $statusCode,
            'error_code' => $errorCode
        ]);

        throw ApiErrorResponse::createErrorResponse($message, $errors, $statusCode, $errorCode);
    }

}