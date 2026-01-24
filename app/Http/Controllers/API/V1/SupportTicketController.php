<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Role;
use App\Enum\Support\Category;
use App\Enum\Support\Priority;
use App\Enum\Support\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SupportTicket\CreateRequest;
use App\Http\Requests\Api\V1\SupportTicket\IndexRequest;
use App\Http\Requests\Api\V1\SupportTicket\ReplyRequest;
use App\Http\Resources\Api\V1\SupportTicket\SupportTicketDetailsResource;
use App\Http\Resources\Api\V1\SupportTicket\TicketsResource;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportTicketController extends Controller
{
    use ApiResponse;

    /**
     * List user's tickets
     */
    public function index(IndexRequest $request)
    {
        try {
            $validated = $request->validated();

            $page    = $validated['page'] ?? 1;
            $perPage = $validated['per_page'] ?? 10;

            $tickets = SupportTicket::with('assignedTo:id,first_name,last_name')
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate($perPage);

            return $this->sendResponse(TicketsResource::collection($tickets), 'Support tickets retrieved successfully',200,[],$tickets);
        }catch (\Exception $e){
            Log::error("Failed to get support tickets",[
                'userId' => auth()->id(),
                'message' => $e->getMessage()
            ]);
            return $this->sendError('Failed to get support tickets',[],500);
        }
    }

    /**
     * Create new support ticket
     * (ONLY ONE OPEN ticket allowed)
     */
    public function store(CreateRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            // get current auth
            $user = auth()->user();

            // Only one open ticket
            $hasOpen = SupportTicket::where('user_id', $user->id)
                ->where('status', Status::OPEN)
                ->exists();

            if ($hasOpen) {
                return $this->sendError('You already have an open support ticket.', [],422);
            }

            $ticket = SupportTicket::create([
                'user_id'  => $user->id,
                'subject'  => $validated['subject'],
                'category' => $validated['category'],
                'priority' => $validated['priority'] ?? 'medium',
                'status'   => Status::OPEN,
            ]);

            // First message
            SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'sender_id'         => $user->id,
                'message'           => $request->message,
            ]);

            // notify admin
            $admin = User::where('role', Role::ADMIN->value)->first();

            if ($admin) {
                $admin->notify(
                    new UniversalNotification(
                        'New Support Ticket Created',
                        "A new support ticket has been created by {$user->first_name} {$user->last_name}. Ticket ID: #{$ticket->id}.",
                        'info',
                        [
                            'ticket_id' => $ticket->id,
                            'user_id'   => $user->id,
                            'priority'  => $ticket->priority,
                            'status'    => $ticket->status,
                            'action'    => 'support_ticket_view',
                        ]
                    )
                );
            }

            DB::commit();
            return $this->sendResponse([], 'Support ticket created successfully');
        }catch (\Exception $e){
            DB::rollBack();
            Log::error("Failed to create support ticket",[
                'userId' => auth()->user()->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to create support ticket.", [], 500);
        }
    }

    /**
     * View single ticket with messages
     */
    public function show(SupportTicket $ticket)
    {
        $this->authorizeTicket($ticket);


        // permission check
        $this->authorizeTicket($ticket);

        // Load data Facebook-comment style
        $ticket->load([
            'user',
            'assignedTo',

            // ONLY root comments
            'messages' => fn ($q) => $q
                ->whereNull('reply_to_id')
                ->oldest(),

            'messages.sender',

            // Replies (1 level only)
            'messages.replies' => fn ($q) => $q->oldest(),
            'messages.replies.sender',
        ]);

        // Mark unread messages as read (user side)
        $ticket->messages()
            ->where('sender_id', '!=', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return $this->sendResponse(
            new SupportTicketDetailsResource($ticket),
            'Support ticket details retrieved'
        );


    }

    /**
     * Reply to ticket
     */
    public function reply(ReplyRequest $request, SupportTicket $ticket)
    {
        try {
            $this->authorizeTicket($ticket);

            $validated = $request->validated();

            // get current auth
            $user = auth()->user();

            if ($ticket->status !== Status::OPEN) {
                return $this->sendError('Ticket is closed.', [], 422);
            }

            $message = SupportMessage::create([
                'support_ticket_id' => $ticket->id,
                'sender_id'         => $user->id,
                'message'           => $validated['message'],
                'reply_to_id'       => $request->reply_to_id,
                ]);

            // update ticket update time
            $ticket->touch();

            // notify admin
            $admin = User::where('role', Role::ADMIN->value)->first();

            if ($admin) {
                $admin->notify(
                    new UniversalNotification(
                        'New Reply on Support Ticket',
                        "{$user->first_name} {$user->last_name} replied to support ticket. Ticket ID: #" . str_pad($ticket->id, 6, '0', STR_PAD_LEFT) . ".",
                        'info',
                        [
                            'ticket_id' => $ticket->id,
                            'user_id'   => $ticket->user_id,
                            'priority'  => $ticket->priority,
                            'status'    => $ticket->status,
                            'action'    => 'support_ticket_view',
                        ]
                    )
                );

            }

            return $this->sendResponse([], 'Reply sent successfully');
        }catch (\Exception $e){
            Log::error("Failed to send reply",[
                'userId' => auth()->user()->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to send reply.", [], 500);
        }
    }

    /**
     * Get support meta data (categories + priorities)
     */
    public function getData(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $data = [
                'categories' => collect(Category::cases())->map(fn ($item) => [
                    'value' => $item->value,
                    'label' => $item->label(),
                ]),

                'priorities' => collect(Priority::cases())->map(fn ($item) => [
                    'value' => $item->value,
                    'label' => $item->label(),
                ]),
            ];
            return $this->sendResponse($data, 'Support metadata retrieved successfully');
        }catch (\Exception $e){
            return $this->sendError("Failed to retrieve data.", [],500);
        }
    }


    /**
     * Authorization helper
     */
    private function authorizeTicket(SupportTicket $ticket): void
    {
        if ($ticket->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access');
        }
    }

}
