<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Wallet\getFilterDataRequest;
use App\Http\Requests\Api\V1\Wallet\getTransactionRequest;
use App\Http\Resources\Api\V1\Wallet\getTransactionResource;
use App\Models\WalletTransaction;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WalletController extends Controller
{
    use ApiResponse;

    /**
     * get wallet filter data
     */
    public function getFilterData(getFilterDataRequest $request)
    {
       $validated = $request->validated();
        try {
            $cacheKey = 'wallet_filter_data_v1';
            $cacheTtl = 3600; // 1 hour
            $cacheReset = $validated['reset'];

            if ($cacheReset) {
                Cache::forget($cacheKey);
            }
            $data = Cache::remember($cacheKey, $cacheTtl, function () {
                return [
                    'type' => collect(TransactionType::cases())->map(fn ($c) => [
                        'label' => ucfirst(strtolower($c->name)),
                        'value' => $c->value,
                    ]),

                    'source' => collect(TransactionSource::cases())->map(fn ($c) => [
                        'label' => ucfirst(str_replace('_', ' ', strtolower($c->name))),
                        'value' => $c->value,
                    ]),

                    'status' => collect(TransactionStatus::cases())->map(fn ($c) => [
                        'label' => ucfirst(strtolower($c->name)),
                        'value' => $c->value,
                    ]),
                ];
            });

            return $this->sendResponse($data, 'Wallet filter data retrieved successfully');
        } catch (\Throwable $e) {
            return $this->sendError('Failed to retrieve wallet filter data',[],500);
        }
    }

    /**
     * My wallet
     */
    public function getWallet()
    {
        try {
            $user = auth()->user();

            $wallet = $user->wallet;
            $manager = $user->manager;

            // custom response
            $data = [
                'balance' => number_format($wallet?->balance ?? 0, 2),
                'last_updated' => $wallet?->updated_at
                    ? $wallet->updated_at->format('M d, Y \a\t H:i')
                    : null,
                'manager' => $manager ? [
                    'id' => $manager->id,
                    'first_name' => $manager->first_name,
                    'last_name' => $manager->last_name,

                    // correct column name
                    'account_number' => $manager->account_number,

                    // generate manager assigned id (MNG-101)
                    'manager_assign_id' => 'MNG-' . str_pad($manager->id, 3, '0', STR_PAD_LEFT),
                ] : null,
            ];

            return $this->sendResponse($data, 'Wallet retrieved successfully');

        } catch (\Throwable $e) {
            return $this->sendError( 'Failed to retrieve wallet information.',[], 500);
        }
    }

    /**
     * list of wallet transaction history
     */
    public function getTransactions(getTransactionRequest $request)
    {
        // get current auth
        $user      = auth()->user();
        $validated = $request->validated();

        $type    = $validated['type'];
        $source  = $validated['source'];
        $status  = $validated['status'];
        $page    = $validated['page'];
        $perPage = $validated['per_page'];


        $transactions = WalletTransaction::with([
            'fromUser:id,first_name,last_name,role',
            'toUser:id,first_name,last_name,role',
        ])
            ->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })

            // Enum filters
            ->when($request->type, fn ($q) =>
            $q->where('type', $request->type)
            )
            ->when($request->source, fn ($q) =>
            $q->where('source', $request->source)
            )
            ->when($request->status, fn ($q) =>
            $q->where('status', $request->status)
            )

            // Date based filter
            ->when($request->from_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->to_date);
            })

            ->orderByDesc('created_at')
            ->paginate($perPage);

        return $this->sendResponse(getTransactionResource::collection($transactions),'Wallet transaction history retrieved successfully');
    }

}
