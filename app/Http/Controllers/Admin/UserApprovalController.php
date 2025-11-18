<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserApprovalController extends Controller
{
    /**
     * Get pending user registrations
     */
    public function getPendingUsers(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $users = User::where('approval_status', 'pending')
            ->where('role', '!=', 'admin') // Admins don't need approval
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Approve a user registration
     */
    public function approveUser(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admin users do not require approval'
            ], 400);
        }

        if ($user->isApproved()) {
            return response()->json([
                'message' => 'User is already approved'
            ], 400);
        }

        $admin = $request->user();
        $oldValues = $user->toArray();

        $user->update([
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Log the approval
        ActivityLog::log('user.approved', $user, $oldValues, $user->toArray(), "User {$user->name} ({$user->email}) approved by admin");

        return response()->json([
            'message' => 'User approved successfully',
            'user' => $user->load('approver'),
        ]);
    }

    /**
     * Reject a user registration
     */
    public function rejectUser(Request $request, User $user): JsonResponse
    {
        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admin users cannot be rejected'
            ], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $admin = $request->user();
        $oldValues = $user->toArray();

        $user->update([
            'approval_status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        // Log the rejection
        ActivityLog::log('user.rejected', $user, $oldValues, $user->toArray(), "User {$user->name} ({$user->email}) rejected by admin: {$request->rejection_reason}");

        return response()->json([
            'message' => 'User rejected successfully',
            'user' => $user->load('approver'),
        ]);
    }

    /**
     * Get approval statistics
     */
    public function getApprovalStats(): JsonResponse
    {
        $stats = [
            'pending' => User::where('approval_status', 'pending')
                ->where('role', '!=', 'admin')
                ->count(),
            'approved' => User::where('approval_status', 'approved')
                ->where('role', '!=', 'admin')
                ->count(),
            'rejected' => User::where('approval_status', 'rejected')
                ->where('role', '!=', 'admin')
                ->count(),
            'by_role' => [
                'farmer' => [
                    'pending' => User::where('role', 'farmer')->where('approval_status', 'pending')->count(),
                    'approved' => User::where('role', 'farmer')->where('approval_status', 'approved')->count(),
                    'rejected' => User::where('role', 'farmer')->where('approval_status', 'rejected')->count(),
                ],
                'buyer' => [
                    'pending' => User::where('role', 'buyer')->where('approval_status', 'pending')->count(),
                    'approved' => User::where('role', 'buyer')->where('approval_status', 'approved')->count(),
                    'rejected' => User::where('role', 'buyer')->where('approval_status', 'rejected')->count(),
                ],
            ],
        ];

        return response()->json($stats);
    }
}

