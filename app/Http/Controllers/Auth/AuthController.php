<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
class AuthController extends Controller
{
    /**
    * Register a new user
    */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|array',
            
            // --- ADD THIS VALIDATION ---
            'role' => ['required', 'string', Rule::in(['farmer', 'buyer'])],
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // --- USE THE ROLE FROM THE REQUEST ---
        $role = $request->role;
        
        // (Optional) Keep your "one farmer" logic if you want
        if ($role === 'farmer' && User::where('role', 'farmer')->exists()) {
            return response()->json([
                'message' => 'A farmer account already exists.'
            ], 422);
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'phone' => $request->phone,
            'address' => $request->address,
            'approval_status' => 'pending', // New registrations require admin approval
        ]);

        // Log the registration
        \App\Models\ActivityLog::log('user.registered', $user, null, $user->toArray(), "New {$role} registration pending approval");
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
        ], 201);
    }
    
    /**
    * Login user
    */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user is approved (admin is always approved)
        if (!$user->isAdmin() && !$user->isApproved()) {
            return response()->json([
                'message' => 'Your account is pending approval. Please wait for administrator approval.',
                'approval_status' => $user->approval_status
            ], 403);
        }
        
        $token = $user->createToken('auth-token')->plainTextToken;
        
        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token
        ]);
    }
    
    /**
    * Logout user
    */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'message' => 'Logout successful'
        ]);
    }
    
    /**
    * Get authenticated user
    */
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()
        ]);
    }
    
    /**
    * Update user profile
    */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|array',
            'address.street' => 'nullable|string',
            'address.city' => 'nullable|string',
            'address.state' => 'nullable|string',
            'address.country' => 'nullable|string',
            'address.postal_code' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user->update($request->only(['name', 'email', 'phone', 'address']));
        
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
    
    /**
    * Change password
    */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $user = $request->user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 400);
        }
        
        $user->update([
            'password' => Hash::make($request->password)
        ]);
        
        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }
}