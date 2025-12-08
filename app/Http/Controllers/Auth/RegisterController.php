<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Handle user registration
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|array',
            'address.street' => 'nullable|string',
            'address.city' => 'nullable|string',
            'address.state' => 'nullable|string',
            'address.country' => 'nullable|string',
            'address.postal_code' => 'nullable|string',
            
            // --- ADD THIS LINE ---
            // Ensures the role is either 'farmer' or 'buyer'
            'role' => ['required', 'string', Rule::in(['farmer', 'buyer'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // --- REPLACE THE OLD ROLE LOGIC WITH THIS ---
        
        // Get the role from the request
        $role = $request->role;

        // The comment in your original file said "only one allowed for each".
        // If you want to enforce that, you can use this logic.
        // If you want to allow MULTIPLE farmers, just delete this "if" block.
        if ($role === 'farmer' && User::where('role', 'farmer')->exists()) {
            // This is just an example. If you want to allow multiple farmers,
            // remove this check.
            return response()->json([
                'message' => 'A farmer account already exists.'
            ], 422);
        }
        // --- END OF REPLACEMENT ---


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role, // <-- This now correctly uses the role from the request
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
}