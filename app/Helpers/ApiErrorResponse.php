<?php

namespace App\Helpers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;

class ApiErrorResponse
{
    const VALIDATION_ERROR_CODE = 'VALIDATION_ERROR';
    const RESOURCE_NOT_FOUND_CODE = 'RESOURCE_NOT_FOUND_ERROR';
    const INVALID_CREDENTIALS_CODE = 'INVALID_CREDENTIALS_ERROR';
    const SMTP_ERROR_CODE = 'SMTP_ERROR';
    const UNAUTHENTICATED_CODE = 'UNAUTHENTICATED_ERROR';
    const UNAUTHORIZED_CODE = 'UNAUTHORIZED_ERROR';
    const UNKNOWN_ROUTE_CODE = 'UNKNOWN_ROUTE_ERROR';
    const TOO_MANY_REQUESTS_CODE = 'TOO_MANY_REQUESTS_ERROR';
    const SERVER_ERROR_CODE = 'SERVER_ERROR';

    /***
     * Creates and returns a custom API error message
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @param String $errorCode
     * @return HttpResponseException
     */
    public static function createErrorResponse(string $message, ?array $errors, int $statusCode, string $errorCode = ApiErrorResponse::SERVER_ERROR_CODE)
    {
        $response = [
            'errorCode' => $errorCode,
            'message' => $message,
            'errors' => $errors
        ];

        if ($statusCode >= 500) Log::error($message);

        return new HttpResponseException(response($response, $statusCode));
    }
}