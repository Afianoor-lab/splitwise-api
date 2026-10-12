<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $code);
    }

    public static function error(string $message = 'Error', int $code = 400, $errors = []): JsonResponse
    {
        // Valid HTTP status code constraint check (100-599)
        $httpCode = ($code >= 100 && $code <= 599) ? $code : 400;

        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $httpCode);
    }
}