<?php

namespace App\Http\Controllers\Web\Backend;

use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    /*
     * list of transaction
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $query = WalletTransaction::with(['fromUser','toUser'])->latest();

            // Status filter
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Type filter
            if ($request->type) {
                $query->where('type', $request->type);
            }

            // Source filter
            if ($request->source) {
                $query->where('source', $request->source);
            }

            // Date range filter
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('created_at', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay(),
                ]);
            }

            return DataTables::of($query)

                ->filter(function ($q) use ($request) {
                    if ($search = $request->search['value'] ?? null) {
                        $q->where(function ($qq) use ($search) {
                            $qq->where('amount', 'like', "%{$search}%")
                                ->orWhere('type', 'like', "%{$search}%")
                                ->orWhere('source', 'like', "%{$search}%")
                                ->orWhere('status', 'like', "%{$search}%")
                                ->orWhereHas('fromUser', function ($u) use ($search) {
                                    $u->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                })
                                ->orWhereHas('toUser', function ($u) use ($search) {
                                    $u->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                });
                        });
                    }
                })


                ->addColumn('TXN', fn ($row) =>
                  "TXN-". str_pad( $row->id,6,'0',STR_PAD_LEFT)
                )

                ->addColumn('from', fn ($row) =>
                $row->fromUser
                    ? $row->fromUser->first_name.' '.$row->fromUser->last_name
                    : '<span class="text-muted">System</span>'
                )

                ->addColumn('to', fn ($row) =>
                $row->toUser
                    ? $row->toUser->first_name.' '.$row->toUser->last_name
                    : '<span class="text-muted">System</span>'
                )

                ->addColumn('amount', fn ($row) =>
                    '<span class="fw-bold">$'.number_format($row->amount,2).'</span>'
                )

                ->addColumn('type', fn ($row) =>
                $row->type === TransactionType::CREDIT->value
                    ? '<span class="badge badge-light-success">Credit</span>'
                    : '<span class="badge badge-light-danger">Debit</span>'
                )

                ->addColumn('source', fn ($row) =>
                    '<span class="badge badge-light-info text-capitalize">'
                    .str_replace('_',' ',$row->source).
                    '</span>'
                )

                ->addColumn('status', fn ($row) =>
                match ($row->status) {
                    TransactionStatus::APPROVED->value =>
                    '<span class="badge badge-light-success">Approved</span>',
                    TransactionStatus::PAID->value =>
                    '<span class="badge badge-light-primary">Paid</span>',
                    TransactionStatus::REJECTED->value =>
                    '<span class="badge badge-light-danger">Rejected</span>',
                    default =>
                    '<span class="badge badge-light-warning">Pending</span>',
                }
                )

                ->addColumn('date', fn ($row) =>
                $row->created_at->format('d M Y, h:i A')
                )

                ->addColumn('action', fn ($row) => '
                    <button class="btn btn-sm btn-light-primary show-transaction"
                        data-id="'.$row->id.'">
                        Show
                    </button>
                ')

                ->rawColumns(['TXN','from','to','amount','type','source','status','action'])
                ->make(true);
        }

        return view('backend.layout.transaction.index');
    }

    /**
     * Show transaction details (AJAX)
     */
    public function show(WalletTransaction $transaction)
    {
        return response()->json([
            'id'     => $transaction->id,
            'from'   => $transaction->fromUser
                ? $transaction->fromUser->first_name.' '.$transaction->fromUser->last_name
                : 'System',
            'to'     => $transaction->toUser
                ? $transaction->toUser->first_name.' '.$transaction->toUser->last_name
                : 'System',
            'amount' => number_format($transaction->amount,2),
            'type'   => ucfirst($transaction->type),
            'source' => ucfirst(str_replace('_',' ',$transaction->source)),
            'status' => ucfirst($transaction->status),
            'date'   => $transaction->created_at->format('d M Y, h:i A'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $query = WalletTransaction::with(['fromUser','toUser'])->latest();

        if ($request->status)  $query->where('status', $request->status);
        if ($request->type)    $query->where('type', $request->type);
        if ($request->source)  $query->where('source', $request->source);

        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        }

        $transactions = $query->get();

        return response()->streamDownload(function () use ($transactions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID','From','To','Amount','Type','Source','Status','Date'
            ]);

            foreach ($transactions as $trx) {
                fputcsv($file, [
                    $trx->id,
                    $trx->fromUser?->first_name.' '.$trx->fromUser?->last_name ?? 'System',
                    $trx->toUser?->first_name.' '.$trx->toUser?->last_name ?? 'System',
                    $trx->amount,
                    $trx->type,
                    $trx->source,
                    $trx->status,
                    $trx->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 'transactions.csv');
    }
}
