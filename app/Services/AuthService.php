<?php

namespace App\Services;

use App\Helper\ResponseHelper;
use App\Http\Resources\AuthResource;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $result = (new AuthResource([
            'user' => $user,
            'token' => $token,
        ]))->toArray($request);

        return ResponseHelper::success(
            'success',
            'User created and token generated successfully',
            $result,
            201
        );
    }

    public function login(LoginRequest $request)
    {
        $data = $request->validated();
        
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return ResponseHelper::error(
                'error',
                'Invalid credentials',
                401
            );
        }

        $token = $user->createToken('api-token')->plainTextToken;

        $result = (new AuthResource([
            'user' => $user,
            'token' => $token,
        ]))->toArray($request);

        return ResponseHelper::success(
            'success',
            'Token generated successfully',
            $result
        );
    }
}

