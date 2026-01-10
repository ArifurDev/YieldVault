<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get last 5 notifications (AJAX)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'unread_count' => 0,
                'notifications' => [],
            ]);
        }

        $notifications = $user->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id'        => $notification->id,
                    'title'     => $notification->data['title'] ?? 'Notification',
                    'message'   => $notification->data['message'] ?? '',
                    'type'      => $notification->data['type'] ?? 'info',
                    'action'    => $notification->data['data']['action'] ?? null,
                    'created'   => $notification->created_at->diffForHumans(),
                    'read_at'   => $notification->read_at,
                ];
            });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }


    /**
     * Read single OR all notifications
     */
    public function readNotifications($id = null)
    {
        $user = Auth::user();

        // ✅ Read single notification
        if ($id) {
            $notification = $user->unreadNotifications()->find($id);

            if (!$notification) {
                return response()->json([
                    'message' => 'Notification not found or already read'
                ], 404);
            }

            $notification->markAsRead();

            return response()->json([
                'message' => 'Notification read successfully'
            ]);
        }

        // ✅ Read all notifications
        if ($user->unreadNotifications->isEmpty()) {
            return response()->json([
                'message' => 'No unread notifications'
            ], 404);
        }

        $user->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}
