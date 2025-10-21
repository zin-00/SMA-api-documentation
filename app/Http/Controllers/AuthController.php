<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * @group Authentication
 *
 * APIs for user registration and login
 */
class AuthController extends Controller
{
    /**
     * Register a new user.
     *
    * @bodyParam name string required The user's name. Example: "Jane Doe"
    * @bodyParam email string required The user's email. Example: "jane@example.com"
    * @bodyParam password string required The user's password. Minimum 6 characters. Example: "secret123"
    *
    * @response 201 {
    *  "message": "User registered successfully",
    *  "user": {
    *      "id": 1,
    *      "name": "Jane Doe",
    *      "email": "jane@example.com",
    *      "created_at": "2025-10-20T00:00:00.000000Z",
    *      "updated_at": "2025-10-20T00:00:00.000000Z"
    *  },
    *  "token": "1|abcdefghijklmnopqrstuvwxyz0123456789"
    * }
    *
    * @response 422 {
    *  "message": "The given data was invalid.",
    *  "errors": {
    *      "email": ["The email has already been taken."]
    *  }
    * }
    *
    * @example request {
    *  "name": "Jane Doe",
    *  "email": "jane@example.com",
    *  "password": "secret123"
    * }
    * @unauthenticated
    */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Log in an existing user.
     *
    * @bodyParam email string required The user's email. Example: "jane@example.com"
    * @bodyParam password string required The user's password. Example: "secret123"
    *
    * @response 200 {
    *  "message": "Login successful",
    *  "user": {
    *      "id": 1,
    *      "name": "Jane Doe",
    *      "email": "jane@example.com",
    *      "created_at": "2025-10-20T00:00:00.000000Z",
    *      "updated_at": "2025-10-20T00:00:00.000000Z"
    *  },
    *  "token": "1|abcdefghijklmnopqrstuvwxyz0123456789"
    * }
    *
    * @response 401 {
    *  "message": "Invalid credentials"
    * }
    *
    * @example request {
    *  "email": "jane@example.com",
    *  "password": "secret123"
    * }
    * @unauthenticated
    */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke token)
     *
     * @authenticated
    *
    * Headers:
    * - Authorization: Bearer {token}
    *
    * @response 200 {
    *  "message": "Logged out successfully"
    * }
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }
}
