<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    /**
     * Get recent notifications for the authenticated user
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        $limit = $request->input('limit', 20);

        // Check if notifications table exists, if not return empty array
        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'notifications' => []
            ], 200);
        }

        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'notification_id' => $notification->id ?? $notification->notification_id,
                    'user_id' => $notification->user_id,
                    'type' => $notification->type ?? 'general',
                    'title' => $notification->title ?? 'Notification',
                    'message' => $notification->message ?? $notification->data ?? '',
                    'data' => is_string($notification->data ?? null) ? json_decode($notification->data, true) : null,
                    'action_url' => $notification->action_url ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ], 200);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();

        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ], 200);
        }

        DB::table('notifications')
            ->where('id', $id)
            ->orWhere('notification_id', $id)
            ->where('user_id', $user->id)
            ->update([
                'read_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ], 200);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ], 200);
        }

        DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ], 200);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        $user = $request->user();

        if (!DB::getSchemaBuilder()->hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'count' => 0
            ], 200);
        }

        $count = DB::table('notifications')
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ], 200);
    }
}
