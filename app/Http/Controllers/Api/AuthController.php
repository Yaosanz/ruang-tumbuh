<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user account and return JWT.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_admin' => false,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 201);
    }

    /**
     * Login and get a JWT token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak tepat.'],
            ]);
        }

        $user = JWTAuth::user();

        return response()->json([
            'message' => 'Login berhasil.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout (invalidate JWT token).
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // Token already invalid or not provided
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token tidak valid atau sudah kedaluwarsa.'], 401);
        }

        return response()->json([
            'message' => 'Token berhasil diperbarui.',
            'token' => $newToken,
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => auth()->user()->only(['id', 'name', 'email', 'role']),
        ]);
    }
}
