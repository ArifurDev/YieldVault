<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Notification\RecentNotificationResource;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ApiResponse;

    /**
     * list of notifications
     */
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // Get the logged-in user
            $user = auth()->user();

            // Get all notifications from the last 7 days
            $notifications = $user->notifications()
                ->where('created_at', '>=', Carbon::now()->subDays(7)) // Filters notifications from the last 7 days
                ->get();


            // Optionally, you can order them by created_at to show the most recent notifications first
            $notifications = $notifications->sortByDesc('created_at');

            // Dynamic message
            $messageKey = $notifications->isEmpty()
                ? 'notifications_empty'
                : 'notifications_list_success';

            $messageText = $notifications->isEmpty()
                ? 'No recent notifications found.'
                : 'Recent notifications retrieved successfully.';

            $message = translateHelper::translateCached(
                $messageKey,
                $messageText,
                $lang
            );

            $data = [
                'has_unread_notifications' => $this->checkHasNewNotification(),
                'notifications' => RecentNotificationResource::collection($notifications),
            ];

            return $this->sendResponse($data,$message);
        } catch (\Throwable $th) {
            //throw $th;
            $message = translateHelper::translateCached(
                'notifications_list_failed',
                'Failed to retrieve notifications.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    // check Has New Notification
    private function checkHasNewNotification(): bool
    {
        $user = auth()->user();
        return $user->notifications()
            ->where('created_at', '>=', Carbon::now()->subDays(7)) // Filters notifications from the last 7 days
            ->whereNull('read_at')
            ->exists();
    }


    /**
     * Read a single or all notifications based on the presence of an ID
     */
    public function readNotifications(Request $request,$id = null)
    {
        $lang = $request->header('Accept-Language', 'en');
        $user = Auth::user();

        // If an ID is provided, mark the single notification as read
        if ($id) {
            // Fetch the notification by ID
            $notification = $user->unreadNotifications()->find($id);

            if (!$notification) {
                $message = translateHelper::translateCached(
                    'notification_not_found_or_read',
                    'Notification not found or already read.',
                    $lang
                );
                return $this->sendError($message, [], 404);
            }

            // Mark the notification as read
            $notification->markAsRead();
            $message = translateHelper::translateCached(
                'notification_read_success',
                'Notification read successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        }

        // If no ID is provided, mark all unread notifications as read
        $unreadNotifications = $user->unreadNotifications;

        if ($unreadNotifications->isEmpty()) {
            $message = translateHelper::translateCached(
                'notifications_not_found',
                'Notifications not found.',
                $lang
            );
            return $this->sendError($message, [], 404);
        }

        // Mark all unread notifications as read
        $unreadNotifications->markAsRead();

        $message = translateHelper::translateCached(
            'notifications_read_success',
            'Notifications read successfully.',
            $lang
        );

        return $this->sendResponse([], $message);
    }
}
