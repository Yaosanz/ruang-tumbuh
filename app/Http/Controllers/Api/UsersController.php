<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * List all users.
     */
    public function index(): JsonResponse
    {
        $users = User::select(['id', 'name', 'email', 'role', 'created_at', 'updated_at'])->latest()->get();
        return response()->json(['users' => $users]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => ['sometimes', 'string', Rule::in(['user', 'admin'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'is_admin' => ($validated['role'] ?? 'user') === 'admin',
        ]);

        return response()->json([
            'message' => 'User berhasil dibuat.',
            'user' => $user->only(['id', 'name', 'email', 'role']),
        ], 201);
    }

    /**
     * Show a single user.
     */
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role', 'created_at', 'updated_at']),
        ]);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'sometimes|string|min:6',
            'role' => ['sometimes', 'string', Rule::in(['user', 'admin'])],
        ]);

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $user->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        if (isset($validated['role'])) {
            $user->role = $validated['role'];
            $user->is_admin = $validated['role'] === 'admin';
        }
        $user->save();

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'user' => $user->fresh()->only(['id', 'name', 'email', 'role']),
        ]);
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(['message' => 'User berhasil dihapus.']);
    }
}
