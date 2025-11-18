<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    /**
     * Get all users with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::paginate($perPage);

        return response()->json($users);
    }

    /**
     * Get a specific user
     */
    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    /**
     * Create a new user
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:farmer,buyer,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check role restrictions - only one admin and one farmer allowed
        $requestedRole = $request->role;
        if ($requestedRole === 'admin' && User::where('role', 'admin')->exists()) {
            return response()->json([
                'message' => 'Only one admin user is allowed'
            ], 422);
        }
        
        if ($requestedRole === 'farmer' && User::where('role', 'farmer')->exists()) {
            return response()->json([
                'message' => 'Only one farmer user is allowed'
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $requestedRole,
            'phone' => $request->phone,
            'address' => $request->address,
            'approval_status' => $requestedRole === 'admin' ? 'approved' : 'pending', // Admins are auto-approved
        ]);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Update a user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|required|string|min:8',
            'role' => 'sometimes|required|in:farmer,buyer,admin',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check role restrictions if role is being updated
        if ($request->has('role') && $request->role !== $user->role) {
            $requestedRole = $request->role;
            
            if ($requestedRole === 'admin' && User::where('role', 'admin')->where('id', '!=', $user->id)->exists()) {
                return response()->json([
                    'message' => 'Only one admin user is allowed'
                ], 422);
            }
            
            if ($requestedRole === 'farmer' && User::where('role', 'farmer')->where('id', '!=', $user->id)->exists()) {
                return response()->json([
                    'message' => 'Only one farmer user is allowed'
                ], 422);
            }
        }

        $updateData = $request->only(['name', 'email', 'role', 'phone', 'address']);
        
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }

    /**
     * Get users by role
     */
    public function getByRole(Request $request, string $role): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::where('role', $role)->paginate($perPage);

        return response()->json($users);
    }
}