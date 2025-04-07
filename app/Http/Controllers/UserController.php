<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'data'    => $validator->errors(),
                'message' => 'Validation error',
            ], 401);
        }

        $user = User::create([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        return response()->json([
            'status'  => 'success',
            'data'    => $user,
            'message' => 'User registered successfully',
        ], 201);
    }

    /**
     * Login a user and return an authentication token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'data'    => $validator->errors(),
                'message' => 'Validation error',
            ], 401);
        }

        // Authenticate user manually
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => 'error',
                'data'    => null,
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Generate a token using Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'data'    => [
                'user'  => $user,
                'token' => $token,
            ],
            'message' => 'User logged in successfully',
        ], 200);
    }

    /**
     * Logout user by revoking the token.
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User logged out successfully',
        ], 200);
    }

    /**
     * Update a user's details.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|min:6',
            'role'     => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'data'    => $validator->errors(),
                'message' => 'Validation error',
            ], 401);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'data'    => null,
                'message' => 'User not found',
            ], 404);
        }

        // Update user fields
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }
        if ($request->has('role')) {
            $user->role = $request->role;
        }

        $user->save();

        return response()->json([
            'status'  => 'success',
            'data'    => $user,
            'message' => 'User updated successfully',
        ], 200);
    }

    /**
     * Delete a user.
     */
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'data'    => null,
                'message' => 'User not found or deletion failed',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'data'    => null,
            'message' => 'User deleted successfully',
        ], 200);
    }
}
