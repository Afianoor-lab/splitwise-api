<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Exception;

class AuthController
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $token = $this->authService->register($request->validated());
            return ApiResponse::success(['api_token' => $token], 'User registered successfully.', 201);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 400);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $token = $this->authService->login($request->validated());
            return ApiResponse::success(['api_token' => $token], 'Login successful.', 200);
        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), $e->getCode() ?: 401);
        }
    }

    public function me(Request $request)
    {
        return ApiResponse::success(['user' => $request->user()], 'User profile fetched.');
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken() ?? $request->header('token');
        $this->authService->logout($token);
        return ApiResponse::success([], 'Logged out successfully.');
    }
}