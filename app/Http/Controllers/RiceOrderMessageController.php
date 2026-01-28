<?php

namespace App\Http\Controllers;

use App\Models\RiceOrder;
use App\Models\RiceOrderMessage;
use Illuminate\Http\Request;

class RiceOrderMessageController extends Controller
{
    public function index(Request $request, RiceOrder $order)
    {
        $user = $request->user();

        if (!$this->canAccessOrder($user, $order)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $order->messages()
            ->with('sender:id,name')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, RiceOrder $order)
    {
        $user = $request->user();

        if (!$this->canAccessOrder($user, $order)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = RiceOrderMessage::create([
            'rice_order_id' => $order->id,
            'sender_id' => $user->id,
            'message' => $validated['message'],
        ])->load('sender:id,name');

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message,
        ], 201);
    }

    protected function canAccessOrder(?\App\Models\User $user, RiceOrder $order): bool
    {
        if (!$user) {
            return false;
        }

        if ($order->buyer_id === $user->id) {
            return true;
        }

        $order->loadMissing('riceProduct');

        return $order->riceProduct && $order->riceProduct->farmer_id === $user->id;
    }
}

