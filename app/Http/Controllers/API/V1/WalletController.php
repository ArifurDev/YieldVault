<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Wallet\depositRequest;
use App\Http\Requests\Api\V1\Wallet\getFilterDataRequest;
use App\Http\Requests\Api\V1\Wallet\getTransactionRequest;
use App\Http\Requests\Api\V1\Wallet\withdrawRequest;
use App\Http\Resources\Api\V1\Wallet\DepositRequestResource;
use App\Http\Resources\Api\V1\Wallet\getTransactionResource;
use App\Http\Resources\Api\V1\Wallet\WithdrawRequestResource;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Models\InvestmentLog;
use App\Models\User;
use App\Models\UserInvestment;
use App\Models\WalletTransaction;
use App\Notifications\UniversalNotification;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    use ApiResponse;

    /**
     * User Dashboard Quick Summary
     */
    public function summary()
    {
        try {
            $user = auth()->user();
            $today = now()->format('Y-m-d');

            // Wallet Balance
            $walletBalance = number_format($user->wallet?->balance ?? 0, 2);

            // Active Investments
            $activeInvestments = UserInvestment::where('user_id', auth()->id())
                ->where('status', 'active')
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->count();

            // today total task
            $totalTasks = DailyTask::where('date', $today)
                ->where('is_active', 1)
                ->count();

            $completedTasks = DailyTaskLog::where('user_id', auth()->id())
                ->whereDate('created_at', today())
                ->count();

            // Today's ROI Potential
            $todayEarnedRoi = InvestmentLog::whereHas('investment', function ($q) {
                $q->where('user_id', auth()->id());
            })
                ->whereDate('date', today())
                ->sum('profit');



            $data = [
                'wallet' => [
                    'balance' => $walletBalance,
                ],
                'investments' => [
                    'active_count' => $activeInvestments,
                ],
                'roi' => [
                    'today_potential' => number_format($todayEarnedRoi, 2),
                ],
                'tasks' => [
                    'completed' => $completedTasks,
                    'total'     => $totalTasks,
                ]
            ];

            return $this->sendResponse($data, 'Dashboard summary retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendError('Failed to load dashboard summary', [], 500);
        }
    }

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

            // Eager load relations
            $user->load([
                'wallet',
                'manager.walletAccounts' => fn ($q) => $q->where('is_active', true)
            ]);

            $wallet = $user?->wallet;
            $manager = $user?->manager;
            // Active manager wallet account (if any)
            $walletAccount = $manager?->walletAccounts->first();

            $pending_withdraw = $user?->withdrawRequests->where('status',TransactionStatus::PENDING->value)->sum('amount');
//            $total_earn = WalletTransaction::where('to_user_id',$user->id)->where('type',TransactionType::CREDIT->value)->where('status',TransactionStatus::APPROVED->value)->sum('amount');
            $total_earn = WalletTransaction::where('to_user_id', $user->id)
                ->where('type', TransactionType::CREDIT->value)
                ->where('status', TransactionStatus::APPROVED->value)
                ->whereIn('source', [
                    TransactionSource::INVESTMENT_PROFIT->value,
                    TransactionSource::REFERRAL_REWARD->value,
                    TransactionSource::TASK_REWARD->value,
                ])
                ->sum('amount');

            // custom response
            $data = [
                'balance' => number_format($wallet?->balance ?? 0, 2),
                'pending_withdraw' => number_format($pending_withdraw ?? 0, 2),
                'total_earn' => number_format($total_earn ?? 0, 2),
                'last_updated' => $wallet?->updated_at
                    ? $wallet->updated_at->format('M d, Y \a\t H:i')
                    : null,
                'manager' => $manager ? [
                    'id' => $manager->id,
                    'first_name' => $manager->first_name,
                    'last_name' => $manager->last_name,
                    // generate manager assigned id (MNG-101)
                    'manager_assign_id' => 'MNG-' . str_pad($manager->id, 3, '0', STR_PAD_LEFT),
                    'wallet_account' => $walletAccount ? [
                        'id'             => $walletAccount->id, // What kind of wallet
                        'method'         => $walletAccount->type,// bank | bkash | nagad | rocket
                        'label'          => $walletAccount->label ?? ($walletAccount->bank_name ?? 'Wallet Account'), // Human readable label

                        'account_number' => $walletAccount->account_number,// Common identifier (bank / mobile)
                        'provider'       => $walletAccount->network,// Only for mobile wallets (bkash/nagad), null for bank
                    ] : null,


                ] : null,
            ];

            return $this->sendResponse($data, 'Wallet retrieved successfully');

        } catch (\Exception $e) {
            Log::error("Failed to retrieve wallet filter data:", [
                'userId' => $user->id,
                'errorMessage' => $e->getMessage(),
            ]);
            return $this->sendError( 'Failed to retrieve wallet information.',[], 500);
        }
    }

    /**
     * list of wallet transaction history
     */
    public function getTransactions(getTransactionRequest $request)
    {
        try {
            // get current auth
            $user      = auth()->user();
            $validated = $request->validated();

            $type    = $validated['type'];
            $source  = $validated['source'];
            $status  = $validated['status'];
            $page    = $validated['page'] ? $validated['page'] : 1;
            $perPage = $validated['per_page'] ? $validated['per_page'] : 10;


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

            return $this->sendResponse(getTransactionResource::collection($transactions),'Wallet transaction history retrieved successfully',200,[],$transactions);
        }catch (\Exception $e) {
            Log::error("Failed to retrieve transactions:", [
                'userId' => $user->id,
                'errorMessage' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to retrieve wallet transactions",[],500);
        }
    }


    /**
     * Deposit Request
     */
    public function depositRequest(depositRequest $request)
    {
        try {
            $validated = $request->validated();
            // get current auth
            $user = auth()->user();

            // check and manager is assigned or not
            if (!$user->manager_id) {
                return $this->sendError('Manager not assigned', [], 422);
            }

            // Get manager
            $manager = User::find($user->manager_id);

            if (!$manager) {
                return $this->sendError('Manager not found', [], 404);
            }

            // Get active manager wallet account
            $walletAccount = $manager->walletAccounts()
                ->where('is_active', true)
                ->first();

            if (!$walletAccount) {
                return $this->sendError('Manager does not have an active wallet account.', [], 422);
            }

            $imagePaths = null;
            // Update avatar if provided
            if ($request->hasFile('proof')) {
                $file = $request->file('proof');
                $mediaFile = 'proof_'.time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $imagePaths = Helpers::uploadFile('proof',$file,$mediaFile,config('app.file_system')); // store url
            }

            $deposit = \App\Models\DepositRequest::create([
                'user_id'       => $user->id,
                'manager_id'    => $manager?->id,
                'manager_wallet_account_id' => $walletAccount->id,

                'amount'        => $validated['amount'],

                // Wallet snapshot (history safe)
                'wallet_method'         => $walletAccount->type,
                'wallet_label'          => $walletAccount->label
                    ?? ($walletAccount->bank_name ?? 'Wallet Account'),
                'wallet_account_number' => $walletAccount->account_number,
                'wallet_provider'       => $walletAccount->network,

                'proof_path'    => $imagePaths,
                'status'        => TransactionStatus::PENDING->value,
                'remarks'       => $validated['remarks'] ?? null,
            ]);

            // Notify manager
            $manager = User::find($user->manager_id);

            if ($manager) {
                $manager->notify(
                    new UniversalNotification(
                        'New Deposit Request',
                        "{$user->first_name} {$user->last_name} has submitted a deposit request of {$deposit->amount}.",
                        'info',
                        [
                            'id'         => $deposit->id,
                            'user_id'    => $user->id,
                            'amount'     => $deposit->amount,
                            'action'     => 'deposit_review',
                        ]
                    )
                );
            }

            return $this->sendResponse( new DepositRequestResource($deposit), 'Deposit request submitted successfully');
        }catch (\Exception $e) {
            Log::error('Deposit request failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return $this->sendError('Failed to submitted deposit request.',[],500);
        }
    }

    /**
     * Withdraw Request
     */
    public function withdrawRequest(withdrawRequest $request)
    {
        try {
            $validated = $request->validated();
            $user = auth()->user();

            if (!$user->manager_id) {
                return $this->sendError('Manager not assigned', [], 422);
            }

            if (($user->wallet?->balance ?? 0) < $validated['amount']) {
                return $this->sendError('Insufficient wallet balance', [], 422);
            }

            $withdraw = \App\Models\WithdrawRequest::create([
                'user_id'    => $user->id,
                'manager_id' => $user->manager_id,
                'amount'     => $validated['amount'],
                'status'     => TransactionStatus::PENDING->value,
                'remarks'    => $validated['remarks'] ?? null,
            ]);

            // 🔔 Notify manager
            $manager = User::find($user->manager_id);
            if ($manager) {
                $manager->notify(
                    new UniversalNotification(
                        'New Withdraw Request',
                        "{$user->first_name} {$user->last_name} has requested a withdrawal of ৳{$withdraw->amount}. Please review.",
                        'info',
                        [
                            'id'          => $withdraw->id,
                            'user_id'     => $user->id,
                            'amount'      => $withdraw->amount,
                            'action'      => 'withdraw_review',
                        ]
                    )
                );
            }

            return $this->sendResponse(new WithdrawRequestResource($withdraw), 'Withdraw request submitted successfully');
        }catch (\Exception $e) {
            Log::error('Deposit request failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);
            return $this->sendError('Failed to submitted Withdraw request.',[],500);
        }
    }
}
