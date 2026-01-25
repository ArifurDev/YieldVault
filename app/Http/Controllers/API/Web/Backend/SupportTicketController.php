<?php

namespace App\Http\Controllers\API\Web\Backend;

use App\Enum\Support\Status;
use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Notifications\UniversalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class SupportTicketController extends Controller
{
    /**
     * list of all tickets
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = SupportTicket::with([
                'user',
                'assignedTo',
                'messages' => fn ($q) => $q->latest()
            ])->latest();

            return DataTables::of($query)

                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($search = $request->get('search')['value']) {
                        $search = trim(str_replace('#', '', $search));
                        $query->where(function ($q) use ($search) {
                            $q->where('id', 'like', "%{$search}%")
                                ->orWhere('subject', 'like', "%{$search}%")
                                ->orWhere('category', 'like', "%{$search}%")
                                ->orWhere('priority', 'like', "%{$search}%")
                                ->orWhere('status', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($u) =>
                                $u->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                )
                                ->orWhereHas('assignedTo', fn ($a) =>
                                $a->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                );
                        });
                    }
                })

                ->addColumn('ticket_no', fn ($t) =>
                    '#' . str_pad($t->id, 6, '0', STR_PAD_LEFT)
                )

                ->addColumn('subject', fn ($t) =>
                   $t->subject ? Str::limit($t->subject, 10, '...') : '-'
                )

                ->addColumn('user', fn ($t) =>
                    $t->user->first_name . ' ' . $t->user->last_name
                )
                ->addColumn('priority', fn ($t) =>
                    '<span class="badge bg-warning">' . ucfirst($t->priority->value) . '</span>'
                )
                ->addColumn('status', function ($t) {
                    return match ($t->status->value) {
                        'open'   => '<span class="badge bg-success">Open</span>',
                        'pending'   => '<span class="badge bg-primary">pending</span>',
                        'closed' => '<span class="badge bg-secondary">Closed</span>',
                    };
                })

                ->addColumn('assigned', fn ($t) =>
                $t->assignedTo
                    ? $t->assignedTo->first_name
                    : '<span class="text-muted">Unassigned</span>'
                )

                ->addColumn('action', fn ($t) => '
                    <a href="' . route('support-tickets.show', $t->id) . '"
                       class="btn btn-sm btn-primary">
                        View
                    </a>
                ')

                ->addColumn('updated_at', function ($t) {
                    return $t->updated_at->diffForHumans();
                })
                ->addColumn('attention', function ($t) {

                    $unread = $t->unreadCountFor(auth()->id());

                    if ($unread > 0) {
                        return '<span class="badge bg-danger">Unread ('.$unread.')</span>';
                    }

                    if ($t->needsReply()) {
                        return '<span class="badge bg-warning text-dark">Needs Reply</span>';
                    }

                    return '<span class="badge bg-success">Replied</span>';
                })


                ->rawColumns(['priority', 'status', 'assigned','attention', 'action'])
                ->make(true);
        }
        return view('backend.layout.support_ticket.index');
    }

    /**
     * Show single ticket with messages
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'user',
            'assignedTo',
            'messages' => fn ($q) => $q->oldest(),
            'messages.sender',
            'messages.replyTo',
        ]);

        // Mark unread messages as read (admin side)
        $ticket->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('backend.layout.support_ticket.show', compact('ticket'));
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message'     => 'required|string',
            'reply_to_id' => 'nullable|exists:support_messages,id',
        ]);

        try {
            $ticket->touch();

            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'sender_id' => auth()->id(),
                'message' => $request->message,
                'reply_to_id' => $request->reply_to_id,
                'is_read' => false,
            ]);

            $ticket->user->notify(
                new UniversalNotification(
                    'Support replied to your ticket',
                    "Your support ticket #{$ticket->id} has a new reply from our support team. Please review the message and reply if needed.",
                    'info',
                    [
                        'ticket_id' => $ticket->id,
                        'priority'  => $ticket->priority,
                        'status'    => $ticket->status,
                        'action'    => 'support_ticket_view',
                    ]
                )
            );

            return redirect()->route('support-tickets.show', $ticket)->with('success', 'Reply sent successfully');
        }catch (\Exception $e){
            return redirect()->route('support-tickets.show', $ticket)->with('error', $e->getMessage());
        }
    }

    public function close(Request $request, SupportTicket $ticket)
    {
        try {
            // already closed?
            if ($ticket->status === Status::CLOSED) {
                return back()->with('error', 'Ticket is already closed.');
            }

            // update status
            $ticket->update([
                'status' => Status::CLOSED,
            ]);

            // optional: notify user
            $ticket->user->notify(
                new UniversalNotification(
                    'Support Ticket Closed',
                    "Your support ticket #" . str_pad($ticket->id, 6, '0', STR_PAD_LEFT) . " has been closed.",
                    'info',
                    [
                        'ticket_id' => $ticket->id,
                        'status' => Status::CLOSED->value,
                    ]
                )
            );

            return back()->with('success', 'Ticket closed successfully.');

        } catch (\Exception $e) {
            \Log::error('Close ticket failed', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to close ticket.');
        }
    }
}
