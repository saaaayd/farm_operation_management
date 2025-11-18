<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laborer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LaborerManagementController extends Controller
{
    /**
     * Get all laborers (FR-A.4)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $query = Laborer::with('user');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by farmer
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $laborers = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($laborers);
    }

    /**
     * Get a specific laborer
     */
    public function show(Laborer $laborer): JsonResponse
    {
        $laborer->load(['user', 'tasks', 'laborWages']);
        return response()->json($laborer);
    }

    /**
     * Create a new laborer (FR-A.4)
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'skill_level' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $laborer = Laborer::create($validator->validated());

        // Log the creation
        ActivityLog::log('laborer.created', $laborer, null, $laborer->toArray(), "Laborer '{$laborer->name}' created by admin");

        return response()->json([
            'message' => 'Laborer created successfully',
            'laborer' => $laborer->load('user'),
        ], 201);
    }

    /**
     * Update a laborer (FR-A.4)
     */
    public function update(Request $request, Laborer $laborer): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'skill_level' => 'nullable|string|max:50',
            'specialization' => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'emergency_contact' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $oldValues = $laborer->toArray();
        $laborer->update($validator->validated());

        // Log the update
        ActivityLog::log('laborer.updated', $laborer, $oldValues, $laborer->toArray(), "Laborer '{$laborer->name}' updated by admin");

        return response()->json([
            'message' => 'Laborer updated successfully',
            'laborer' => $laborer->load('user'),
        ]);
    }

    /**
     * Delete a laborer (FR-A.4)
     */
    public function destroy(Laborer $laborer): JsonResponse
    {
        $oldValues = $laborer->toArray();
        $name = $laborer->name;

        $laborer->delete();

        // Log the deletion
        ActivityLog::log('laborer.deleted', null, $oldValues, null, "Laborer '{$name}' deleted by admin");

        return response()->json([
            'message' => 'Laborer deleted successfully',
        ]);
    }
}

