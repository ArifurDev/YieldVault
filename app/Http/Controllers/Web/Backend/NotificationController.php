<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class NotificationController extends Controller
{
    /**
     * all notification show
     */
    public function allNotifications(Request $request)
    {
        if ($request->ajax()) {

            $query = Auth::user()
                ->notifications()
                ->select([
                    'id',
                    'type',
                    'data',
                    'read_at',
                    'created_at'
                ])
                ->latest();

            return DataTables::of($query)

                /*  Global Search (title + message) */
                ->filter(function ($query) use ($request) {
                    if (!empty($request->search['value'])) {
                        $keyword = $request->search['value'];

                        $query->where(function ($q) use ($keyword) {
                            $q->where('data->title', 'like', "%{$keyword}%")
                                ->orWhere('data->message', 'like', "%{$keyword}%");
                        });
                    }
                })

                /* Status Filter (Read / Unread) */
                ->filterColumn('status', function ($query, $keyword) {
                    if ($keyword === 'read') {
                        $query->whereNotNull('read_at');
                    }

                    if ($keyword === 'unread') {
                        $query->whereNull('read_at');
                    }
                })

                ->addColumn('title', function ($row) {
                    return $row->data['title'] ?? 'Notification';
                })

                ->addColumn('message', function ($row) {
                    return $row->data['message'] ?? '-';
                })

                ->addColumn('status', function ($row) {
                    return $row->read_at
                        ? '<span class="badge badge-light-success">Read</span>'
                        : '<span class="badge badge-light-warning">Unread</span>';
                })

                ->addColumn('time', function ($row) {
                    return $row->created_at->diffForHumans();
                })

                ->addColumn('actions', function ($row) {
                    return '
                    <button
                        data-id="'.$row->id.'"
                        class="btn btn-sm btn-light-primary mark-read">
                        Mark Read
                    </button>
                ';
                })

                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('backend.layout.notification.index');
    }

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

        //  Read single notification
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
