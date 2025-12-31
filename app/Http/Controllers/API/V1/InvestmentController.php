<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Investment\Status;
use App\Enum\Wallet\TransactionSource;
use App\Enum\Wallet\TransactionStatus;
use App\Enum\Wallet\TransactionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Investment\InvestNowRequest;
use App\Models\Package;
use App\Models\User;
use App\Models\UserInvestment;
use App\Models\WalletTransaction;
use App\Traits\ApiResponse;
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
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $user  = auth()->user();
            $admin = User::where('role', 'admin')->firstOrFail();
            $package = Package::with('level')->findOrFail($validated['package_id']);

            if ($user->wallet->balance < $package->price) {
                return $this->sendError('Insufficient wallet balance',[],422);
            }

            DB::transaction(function () use ($user, $admin, $package) {

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
                    'source'       => TransactionSource::INVESTMENT->value,
                    'status'       => TransactionStatus::APPROVED->value,
                    'reference_id' => $investment->id,
                ]);
            });

            DB::commit();
            return $this->sendResponse([], 'Investment started successfully');
        }catch (\Exception $e){
            DB::rollBack();
            Log::error('Investment start failed ', [
                'user_id' => auth()->user()->id,
                'exception' => $e->getMessage()
            ]);
            return $this->sendError('Failed to start investment:'.$e->getMessage(), [], 500);
        }
    }

    /**
     * My Investments
     */
    public function myInvestments()
    {
        $investments = UserInvestment::with('package')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $investments,
        ]);
    }
}
