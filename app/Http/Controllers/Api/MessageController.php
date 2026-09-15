<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['listing:id,title,slug', 'sender:id,name', 'receiver:id,name'])
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                $otherId = $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
                return "{$msg->listing_id}_{$otherId}";
            })
            ->map(function ($messages) use ($userId) {
                $latest = $messages->first();
                $unread = $messages->where('receiver_id', $userId)->where('is_read', false)->count();
                return [
                    'listing' => $latest->listing,
                    'other_user' => $latest->sender_id === $userId ? $latest->receiver : $latest->sender,
                    'last_message' => $latest,
                    'unread_count' => $unread,
                ];
            })
            ->values();

        return response()->json($conversations);
    }

    public function thread(Request $request, int $userId, int $listingId): JsonResponse
    {
        $me = $request->user()->id;

        $messages = Message::where('listing_id', $listingId)
            ->where(function ($q) use ($me, $userId) {
                $q->where(function ($q2) use ($me, $userId) {
                    $q2->where('sender_id', $me)->where('receiver_id', $userId);
                })->orWhere(function ($q2) use ($me, $userId) {
                    $q2->where('sender_id', $userId)->where('receiver_id', $me);
                });
            })
            ->with(['sender:id,name'])
            ->orderBy('created_at')
            ->get();

        // Mark unread as read
        Message::where('listing_id', $listingId)
            ->where('sender_id', $userId)
            ->where('receiver_id', $me)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'listing_id' => 'required|exists:listings,id',
            'receiver_id' => 'required|exists:users,id',
            'body' => 'required|string|max:2000',
        ]);

        if ($data['receiver_id'] == $request->user()->id) {
            return response()->json(['message' => 'Ne možete slati poruke sebi.'], 422);
        }

        $message = Message::create([
            'listing_id' => $data['listing_id'],
            'sender_id' => $request->user()->id,
            'receiver_id' => $data['receiver_id'],
            'body' => $data['body'],
        ]);

        return response()->json($message->load('sender:id,name'), 201);
    }

    public function markRead(Request $request, Message $message): JsonResponse
    {
        if ($message->receiver_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $message->markAsRead();

        return response()->json(['message' => 'Pročitano.']);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Message::where('receiver_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}
