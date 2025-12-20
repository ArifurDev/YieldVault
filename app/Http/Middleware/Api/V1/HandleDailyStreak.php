<?php

namespace App\Http\Middleware\Api\V1;

use App\Helpers\helpers;
use App\Models\DailyTaskLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleDailyStreak
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only for authenticated user
        if (!auth()->check()) {
            return $response;
        }

        // get current auth
        $userId = auth()->user()->id;

        // STREAK INFO
        $streakDays = helpers::calculateStreak($userId);
        $streakXpPerDay = 5; // configurable

        $dailyLogs = DailyTaskLog::whereNotNull('streak')->count();



        return $next($request);
    }
}
