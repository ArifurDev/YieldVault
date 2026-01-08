<?php

namespace App\Jobs;

use App\Enum\Investment\Status;
use App\Models\InvestmentLog;
use App\Models\UserInvestment;
use App\Notifications\UniversalNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessDailyInvestmentROI implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function handle()
    {
        $today = now()->toDateString();

        $investments = UserInvestment::where('status', Status::ACTIVE->value)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->get();

        foreach ($investments as $investment) {

            // Prevent duplicate ROI
            if (
                InvestmentLog::where('user_investment_id', $investment->id)
                    ->whereDate('date', $today)
                    ->exists()
            ) {
                continue;
            }

            DB::transaction(function () use ($investment, $today) {
                // Daily Profit = (amount × roi_percent / 100) / duration_days

                $dailyProfit = round(
                    ($investment->amount * $investment->roi_percent / 100)
                    / $investment->duration_days,
                    2
                );

                $maxProfit = round(
                    ($investment->amount * $investment->roi_percent) / 100,
                    2
                );

                // Safety: stop over-pay
                if ($investment->earned_amount >= $maxProfit) {
                    $investment->update([
                        'status' => Status::COMPLETED->value
                    ]);
                    return;
                }

                InvestmentLog::create([
                    'user_investment_id' => $investment->id,
                    'date' => $today,
                    'profit' => $dailyProfit,
                ]);

                $investment->increment('earned_amount', $dailyProfit);

                // Auto complete
                if (now()->isSameDay($investment->end_date)) {
                    $investment->update([
                        'status' => Status::COMPLETED->value
                    ]);
                }
            });
        }
    }
    public function failed(\Throwable $exception): void
    {
        $admin = \App\Models\User::where('role', 'admin')->first();

        if ($admin) {
            $admin->notify(
                new UniversalNotification(
                    'ROI Job Failed',
                    $exception->getMessage(),
                    'danger',
                    [
                        'job' => self::class,
                        'action' => 'queue_failed',
                    ]
                )
            );
        }
    }

}
