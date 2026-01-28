<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Payment\Category;
use App\Enum\Payment\IssueType;
use App\Enum\Payment\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentReport;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Notifications\UniversalNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PaymentReportController extends Controller
{
    /**
     * list of payment reports
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = PaymentReport::with([
                'user:id,first_name,last_name',
                'manager:id,first_name,last_name',
                'transaction:id,amount',
                'ticket:id'
            ])->latest();

            return DataTables::of($query)

                ->addIndexColumn()

                /* 🔍 Global Search */
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {

                        $query->where(function ($q) use ($search) {
                            $q->where('id', 'like', "%{$search}%")
                                ->orWhere('message', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($u) use ($search) {
                                    $u->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('manager', function ($m) use ($search) {
                                    $m->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%");
                                });
                        });
                    }
                })

                /* Report ID */
                ->addColumn('report_id', fn ($r) =>
                    'PR-' . str_pad($r->id, 6, '0', STR_PAD_LEFT)
                )

                /* Ticket No */
                ->addColumn('ticket_no', fn ($r) =>
                    '#' . str_pad($r->ticket_id, 6, '0', STR_PAD_LEFT)
                )

                /* User */
                ->addColumn('user', fn ($r) =>
                $r->user
                    ? $r->user->first_name . ' ' . $r->user->last_name
                    : '-'
                )

                /* Category (ENUM SAFE) */
                ->addColumn('category', fn ($r) =>
                    $r->category?->label() ?? '-'
                )

                /* Issue Type (ENUM SAFE) */
                ->addColumn('issue_type', fn ($r) =>
                    $r->issue_type?->label() ?? '-'
                )

                /* Amount */
                ->addColumn('amount', fn ($r) =>
                $r->transaction
                    ? '$' . number_format($r->transaction->amount, 2)
                    : '<span class="text-muted">—</span>'
                )

                /* Manager */
                ->addColumn('manager', fn ($r) =>
                $r->manager
                    ? $r->manager->first_name . ' ' . $r->manager->last_name
                    : '<span class="text-muted">Unassigned</span>'
                )

                /* Status (ENUM SAFE) */
                ->addColumn('status', function ($r) {
                    return match ($r->status) {
                        ReportStatus::OPEN =>
                        '<span class="badge bg-primary">Open</span>',

                        ReportStatus::IN_PROGRESS =>
                        '<span class="badge bg-warning text-dark">In Progress</span>',

                        ReportStatus::RESOLVED =>
                        '<span class="badge bg-success">Resolved</span>',

                        ReportStatus::REJECTED =>
                        '<span class="badge bg-danger">Rejected</span>',
                    };
                })

                /* Created At */
                ->addColumn('created_at', fn ($r) =>
                $r->created_at->diffForHumans()
                )

                /* Action */
                ->addColumn('action', fn ($r) => '
                <a href="' . route('payment-reports.show', $r->id) . '"
                   class="btn btn-sm btn-primary">
                    View
                </a>
            ')

                ->rawColumns(['amount', 'manager', 'status', 'action'])
                ->make(true);
        }
        return view('backend.layout.payment_reports.index');
    }

    /**
     * show crate page
     */
    public function create(SupportTicket $ticket)
    {
        // Only payment-related tickets
        abort_if(
            !in_array($ticket->category->value, ['deposit', 'withdrawal']),
            403
        );

        $userId = $ticket->user_id;

        // Ticket user
        $user = User::with('manager')->findOrFail($userId);

        // Auto assigned manager (from user)
        $manager = $user->manager; // can be null

        // Related transactions
        $transactions = WalletTransaction::where(function ($q) use ($userId) {
            $q->where('from_user_id', $userId)
                ->orWhere('to_user_id', $userId);
        })
            ->whereIn('source', ['deposit', 'withdraw'])
            ->latest()
            ->get();

        return view('backend.layout.payment_reports.create', compact(
            'ticket',
            'user',
            'manager',
            'transactions'
        ));
    }


    /**
     * store data in database
     */
    public function store(Request $request)
    {
        $request->validate([
            'ticket_id'      => ['required', 'exists:support_tickets,id'],
            'transaction_id' => ['nullable', 'exists:wallet_transactions,id'],
            'issue_type'     => ['required', 'in:delay,failed,mismatch'],
            'message'        => ['required', 'string', 'max:2000'],
            'manager_id'     => ['required', 'exists:users,id'],
        ]);

        DB::beginTransaction();

        try {
            $ticket = SupportTicket::with('user')->findOrFail($request->ticket_id);

            // Business rules
            if(!in_array($ticket->category->value, ['deposit', 'withdrawal'])){
                return redirect()->back()->with('error', 'Invalid ticket category for payment report.');
            }

            if($ticket->user->role === \App\Enum\Role::MANAGER){
                return redirect()->back()->with('error', 'Invalid ticket category for payment report.');
            }

            if ($ticket->paymentReport){
                return redirect()->back()->with('error', 'Payment report already exists for this ticket.');
            }

            // Auto manager (extra safety)
            $managerId = $request->manager_id
                ?? optional($ticket->user->manager)->id;

            if (!$managerId){
                return redirect()->back()->with('error', 'No manager assigned to this user.');
            }

            // Create report
            PaymentReport::create([
                'ticket_id'      => $ticket->id,
                'user_id'        => $ticket->user_id,
                'manager_id'     => $managerId,
                'transaction_id' => $request->transaction_id,
                'category'       => $ticket->category->value,
                'issue_type'     => $request->issue_type,
                'message'        => $request->message,
                'status'         => ReportStatus::OPEN,
                'created_by'     => auth()->id(),
            ]);

            //


            // Notify MANAGER
            $manager = User::find($managerId);

            $manager?->notify(
                new UniversalNotification(
                    'New Payment Report Assigned',
                    "A payment report has been assigned to you for ticket #"
                    . str_pad($ticket->id, 6, '0', STR_PAD_LEFT)
                    . ". Please review and take action.",
                    'info',
                    [
                        'payment_report_id' => $paymentReport->id ?? null,
                        'ticket_id'         => $ticket->id,
                        'action'            => 'payment_report_view',
                    ]
                )
            );

            // Notify USER
            $ticket->user->notify(
                new UniversalNotification(
                    'Payment Issue Under Review',
                    "Your payment-related issue for ticket #"
                    . str_pad($ticket->id, 6, '0', STR_PAD_LEFT)
                    . " is now under review by our payment team.",
                    'info',
                    [
                        'ticket_id' => $ticket->id,
                        'action'    => 'support_ticket_view',
                    ]
                )
            );


            DB::commit();

            return redirect()
                ->route('support-tickets.show', $ticket->id)
                ->with('success', 'Payment report created successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->withInput()
                ->with('error', 'Failed to create payment report.');
        }
    }

    /**
     * show payment report
     */
    public function show(PaymentReport $paymentReport)
    {
        $paymentReport->load([
            'user:id,first_name,last_name,email,avatar',
            'manager:id,first_name,last_name,email',
            'transaction:id,amount',
            'ticket:id'
        ]);

        return view('backend.layout.payment_reports.show', compact('paymentReport'));
    }

}
