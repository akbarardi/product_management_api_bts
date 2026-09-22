<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->username,
            'username' => $request->username,
            'password' => $request->password,
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (! $accessToken = auth('api')->attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = auth('api')->user();
        $refreshTtl = (int) config('jwt.refresh_ttl', 20160);

        JWTAuth::factory()->setTTL($refreshTtl);
        $refreshToken = JWTAuth::customClaims(['type' => 'refresh'])->fromUser($user);
        JWTAuth::factory()->setTTL((int) config('jwt.ttl', 60));

        return response()->json([
            'authentication_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl', 60) * 60,
        ]);
    }
}
