<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Display all notifications for the authenticated user
     */
    public function index()
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate(20);

        $unreadCount = Notification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread notifications count (for AJAX)
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notification::where('user_id', auth()->id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (for dropdown)
     */
    public function recent(): JsonResponse
    {
        $notifications = Notification::where('user_id', auth()->id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(Notification $notification)
    {
        // Authorization check
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        }

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $updated = Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$updated} notifications marked as read"
            ]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    /**
     * Delete a specific notification
     */
    public function destroy(Notification $notification)
    {
        // Authorization check
        if ($notification->user_id !== auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
        }

        return redirect()->back()->with('success', 'Notification deleted');
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead()
    {
        $deleted = Notification::where('user_id', auth()->id())
            ->read()
            ->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "{$deleted} notifications deleted"
            ]);
        }

        return redirect()->back()->with('success', 'All read notifications deleted');
    }

    /**
     * Show a specific notification and mark as read
     */
    public function show(Notification $notification)
    {
        // Authorization check
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        // Mark as read
        $notification->markAsRead();

        // Redirect to action URL if exists and valid
        if (!empty($notification->action_url)) {
            $actionUrl = $notification->action_url;
            // If it's an absolute URL, parse and compare host. If host differs from current host,
            // redirect only to the path+query so we stay on the current host (avoid APP_URL pointing at another IP).
            if (filter_var($actionUrl, FILTER_VALIDATE_URL) !== false) {
                $parts = parse_url($actionUrl);
                $actionHost = $parts['host'] ?? null;
                $currentHost = request()->getHost();

                if ($actionHost && $actionHost !== $currentHost) {
                    $path = $parts['path'] ?? '/';
                    $query = isset($parts['query']) ? ('?' . $parts['query']) : '';
                    return redirect($path . $query);
                }

                // Same host — safe to redirect to full URL
                return redirect($actionUrl);
            }

            // Not an absolute URL (relative path) — redirect as-is
            return redirect($actionUrl);
        }

        // Fallback to notifications index if no valid action URL
        return redirect()->route('notifications.index')->with('info', 'Notification marked as read');
    }
}
