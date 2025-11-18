<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiceOrder;
use App\Models\RiceOrderMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessageModerationController extends Controller
{
    /**
     * Get all order messages for moderation (FR-C.3)
     */
    public function getAllMessages(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $query = RiceOrderMessage::with(['order.buyer', 'order.riceProduct.farmer', 'sender']);

        // Filter by order
        if ($request->has('order_id')) {
            $query->where('rice_order_id', $request->order_id);
        }

        // Filter by sender
        if ($request->has('sender_id')) {
            $query->where('sender_id', $request->sender_id);
        }

        // Search in message content
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('message', 'like', "%{$search}%");
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($messages);
    }

    /**
     * Get messages for a specific order (FR-C.3)
     */
    public function getOrderMessages(RiceOrder $order): JsonResponse
    {
        $messages = RiceOrderMessage::where('rice_order_id', $order->id)
            ->with(['sender', 'order.buyer', 'order.riceProduct.farmer'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'order' => $order->load(['buyer', 'riceProduct.farmer']),
            'messages' => $messages,
        ]);
    }

    /**
     * Delete a message (for dispute resolution)
     */
    public function deleteMessage(Request $request, RiceOrderMessage $message): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $oldValues = $message->toArray();
        $message->delete();

        // Log the deletion
        \App\Models\ActivityLog::log('message.deleted', null, $oldValues, null, "Message deleted by admin: {$request->reason}");

        return response()->json([
            'message' => 'Message deleted successfully',
        ]);
    }

    /**
     * Get orders with message activity (for monitoring)
     */
    public function getActiveConversations(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        
        // Get orders with recent messages
        $orders = RiceOrder::whereHas('messages', function($query) {
                $query->where('created_at', '>=', now()->subDays(7));
            })
            ->with(['buyer', 'riceProduct.farmer', 'messages' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return response()->json($orders);
    }
}

