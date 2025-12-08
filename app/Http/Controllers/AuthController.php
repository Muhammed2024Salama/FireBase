<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, AuthService $authService)
    {
        return $authService->register($request);
    }

    public function login(LoginRequest $request, AuthService $authService)
    {
        return $authService->login($request);
    }
}

