<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->userNotifications()->latest();

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        return response()->json([
            'unread_count' => $user->userNotifications()->whereNull('read_at')->count(),
            'notifications' => $query->paginate(15),
        ]);
    }

    public function markRead(Request $request, UserNotification $notification): JsonResponse
    {
        // Hanya pemilik notifikasi yang boleh menandainya
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        if (! $notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }

        return response()->json($notification);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()
            ->userNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}