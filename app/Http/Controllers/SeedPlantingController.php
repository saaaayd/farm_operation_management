<?php

namespace App\Http\Controllers;

use App\Models\SeedPlanting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeedPlantingController extends Controller
{
    public function index()
    {
        $seedPlantings = SeedPlanting::where('user_id', Auth::id())
            ->with(['riceVariety'])
            ->latest('planting_date')
            ->get();

        return response()->json($seedPlantings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rice_variety_id' => 'required|exists:rice_varieties,id',
            'planting_date' => 'required|date',
            'expected_transplant_date' => 'nullable|date|after:planting_date',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $seedPlanting = SeedPlanting::create([
            'user_id' => Auth::id(),
            ...$validated,
            'status' => 'sown'
        ]);

        return response()->json($seedPlanting, 201);
    }

    public function show(SeedPlanting $seedPlanting)
    {
        if ($seedPlanting->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($seedPlanting->load('riceVariety', 'plantings'));
    }

    public function update(Request $request, SeedPlanting $seedPlanting)
    {
        if ($seedPlanting->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rice_variety_id' => 'exists:rice_varieties,id',
            'planting_date' => 'date',
            'expected_transplant_date' => 'nullable|date|after:planting_date',
            'quantity' => 'numeric|min:0',
            'unit' => 'string',
            'status' => 'in:sown,germinating,ready,transplanted,failed',
            'notes' => 'nullable|string',
        ]);

        $seedPlanting->update($validated);

        return response()->json($seedPlanting);
    }

    public function destroy(SeedPlanting $seedPlanting)
    {
        if ($seedPlanting->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $seedPlanting->delete();

        return response()->json(['message' => 'Seed planting deleted successfully']);
    }

    /**
     * Get ready seed plantings for transplanting
     */
    public function getReady()
    {
        $ready = SeedPlanting::where('user_id', Auth::id())
            ->where('status', 'ready')
            ->with('riceVariety')
            ->get();

        return response()->json($ready);
    }
}
