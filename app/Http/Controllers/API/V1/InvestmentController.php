<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Investment\Status;
use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Investment\getInvestmentRequest;
use App\Http\Requests\Api\V1\Investment\InvestNowRequest;
use App\Http\Resources\Api\V1\Investment\getInvestmentResource;
use App\Models\InvestmentLog;
use App\Models\Package;
use App\Models\User;
use App\Models\UserInvestment;
use App\Models\WalletTransaction;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvestmentController extends Controller
{
    use ApiResponse;

    /**
     * Invest Now (USER → ADMIN)
     */
    public function investNow(InvestNowRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $user  = auth()->user();
            $admin = User::where('role', 'admin')->firstOrFail();
            $package = Package::with('level')->findOrFail($validated['package_id']);

            if ($user->wallet->balance < $package->price) {
                $message = translateHelper::translateCached(
                    'wallet_insufficient_balance',
                    'Insufficient wallet balance.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            DB::transaction(function () use ($user, $admin, $package) {
                // Ensure wallets exist
                $userWallet = $user->wallet()->firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0]
                );

                $adminWallet = $admin->wallet()->firstOrCreate(
                    ['user_id' => $admin->id],
                    ['balance' => 0]
                );

                // user → admin wallet
                $user->wallet->decrement('balance', $package->price);
                $admin->wallet->increment('balance', $package->price);

                // create investment
                $investment = UserInvestment::create([
                    'user_id'       => $user->id,
                    'package_id'    => $package->id,
                    'amount'        => $package->price,
                    'roi_percent'   => $package->level->daily_roi,
                    'duration_days' => $package->duration_days,
                    'start_date'    => now(),
                    'end_date'      => now()->addDays($package->duration_days),
                    'status'        => Status::ACTIVE->value,
                ]);

                // ledger
                WalletTransaction::create([
                    'from_user_id' => $user->id,
                    'to_user_id'   => $admin->id,
                    'amount'       => $package->price,
                    'type'         => TransactionType::DEBIT->value,
                    'source'       => TransactionSource::INVESTMENT_BUY->value,
                    'status'       => TransactionStatus::APPROVED->value,
                    'reference_id' => $investment->id,
                ]);
            });

            DB::commit();
            $message = translateHelper::translateCached(
                'investment_started_success',
                'Investment started successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Investment start failed ', [
                'user_id' => auth()->user()->id,
                'exception' => $e->getMessage()
            ]);

            $message = translateHelper::translateCached(
                'investment_start_failed',
                'Failed to start investment.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * My Investments
     */
    public function myInvestments(getInvestmentRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');

        try {
            $validated = $request->validated();
            $page      = $validated['page'] ?? 1;
            $perPage   = $validated['per_page'] ?? 10;

            $investments = UserInvestment::with('package','package.level')
                ->where('user_id', auth()->id())
                ->latest()->paginate($perPage);

            $message = translateHelper::translateCached(
                'investment_list_success',
                'My investments retrieved successfully.',
                $lang
            );

            return $this->sendResponse( getInvestmentResource::collection($investments),$message, 200, [], $investments);
        }catch (\Exception $e){
            Log::error('Investment list failed ', [
                'user_id' => auth()->user()->id,
                'exception' => $e->getMessage()
            ]);
            $message = translateHelper::translateCached(
                'investment_list_failed',
                'Failed to list investments.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * investment Summary
     */
    public function investmentSummary(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $userId = auth()->id();

            if (auth()->user()->role->value !== 'user')
            {
                $message = translateHelper::translateCached(
                    'investment_summary_permission_denied',
                    'You do not have permission to view investment summary.',
                    $lang
                );

                return $this->sendError($message, [], 403);
            }

            //Total Invested
            $totalInvested = UserInvestment::where('user_id', $userId)
                ->sum('amount');

            // Average ROI (only ACTIVE investments)
            $averageRoi = UserInvestment::where('user_id', $userId)
                ->where('status', Status::ACTIVE->value)
                ->avg('roi_percent');

            // Today's Profit (from investment_logs)
            $todayProfit = InvestmentLog::whereHas('investment', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
                ->whereDate('date', today())
                ->sum('profit');

            // Next Return (next active investment)
            $nextInvestment = UserInvestment::where('user_id', $userId)
                ->where('status', Status::ACTIVE->value)
                ->orderBy('end_date')
                ->first();

            $nextReturn = null;

            if ($nextInvestment) {
                $dailyProfit = ($nextInvestment->amount * $nextInvestment->roi_percent) / 100;

                $nextReturn = [
                    'date'   => Carbon::tomorrow()->toDateString(),
                    'amount' => number_format($dailyProfit, 2),
                ];
            }

            $data = [
                'total_invested' => number_format($totalInvested, 2),
                'average_roi'    => number_format($averageRoi ?? 0, 2),
                'today_profit'   => number_format($todayProfit, 2),
                'next_return'    => $nextReturn,
            ];


            $message = translateHelper::translateCached(
                'investment_summary_success',
                'Investment summary retrieved successfully.',
                $lang
            );

            return $this->sendResponse($data, $message);

        } catch (\Exception $e) {
            Log::error('Investment summary error', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            $message = translateHelper::translateCached(
                'investment_summary_failed',
                'Failed to retrieve investment summary.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }
}
