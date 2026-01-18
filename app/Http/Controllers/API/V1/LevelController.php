<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Level\getLevelsResource;
use App\Models\DailyTaskLog;
use App\Models\Level;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LevelController extends Controller
{
    use ApiResponse;

    /**
     * get all levels
     */
    public function getLevels(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            Log::info('duplicate route accessed');
            // get current user id
            $userId = auth()->user()->id;

            // Total earned XP
            $earnedXp = DailyTaskLog::where('user_id', $userId)->sum('xp_rewarded');
            $userXp = $earnedXp ?? 0;

            // Active levels ordered
            $levels = Level::where('is_active', true)
                ->orderBy('order')
                ->get();

            // Current level (highest unlocked)
            $currentLevel = $levels
                ->where('required_xp', '<=', $userXp)
                ->last();

            // Next level
            $nextLevel = $levels
                ->where('required_xp', '>', $userXp)
                ->first();

            // Progress calculation
            $requiredXp = $nextLevel?->required_xp ?? $currentLevel?->required_xp ?? 0;

            $progressPercent = $requiredXp > 0
                ? round(($userXp / $requiredXp) * 100)
                : 100;

            $xpToNextLevel = max(0, $requiredXp - $userXp);

            /**
             *  STREAK INFO
             */
            $streakDays = helpers::calculateStreak($userId);
            $streakXpPerDay = 5; // configurable
            $streakBonusXp = $streakDays * $streakXpPerDay;

            // custom data
            $data = [
                'current_progress' => [
                    'current_xp'        => $userXp,
                    'current_level'     => $currentLevel ? [
                        'id'        => $currentLevel->id,
                        'name'      => $currentLevel->name ?  translateHelper::translateCached(
                            'task_title_' . md5($currentLevel->id),
                            $currentLevel->name,
                            $lang
                        ) : null,
                        'daily_roi' => $currentLevel->daily_roi,
                        'icon'      => asset($currentLevel->icon),
                    ] : null,

                    'next_level' => $nextLevel ? [
                        'id'          => $nextLevel->id,
                        'name'        => $nextLevel->name ?  translateHelper::translateCached(
                            'task_title_' . md5($nextLevel->id),
                            $nextLevel->name,
                            $lang
                        ) : null,
                        'required_xp' => $nextLevel->required_xp,
                    ] : null,

                    'required_xp'       => $requiredXp,
                    'progress_percent'  => min($progressPercent, 100),
                    'xp_to_next_level'  => $xpToNextLevel,
                    //  NEW STREAK STACK
                    'streak' => [
                        'days'        => $streakDays,
                        'bonus_xp'    => $streakBonusXp,
                        'xp_per_day'  => $streakXpPerDay,
                    ],
                ],
                'levels' => getLevelsResource::collection($levels)
            ];

            $message = translateHelper::translateCached(
                'levels_retrieved_success',
                'Levels retrieved successfully.',
                $lang
            );

            return $this->sendResponse($data, $message);
        }catch (\Exception $e){
            Log::error('Failed to retrieve levels', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            $message = translateHelper::translateCached(
                'levels_retrieved_failed',
                'Failed to retrieve levels. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }
    /**
     *  get current progress
     */
    public function currentProgress(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // get current user id
            $userId = auth()->user()->id;

            // Total earned XP
            $earnedXp = DailyTaskLog::where('user_id', $userId)->sum('xp_rewarded');
            $userXp = $earnedXp ?? 0;

            // Active levels ordered
            $levels = Level::where('is_active', true)
                ->orderBy('order')
                ->get();

            // Current level (highest unlocked)
            $currentLevel = $levels
                ->where('required_xp', '<=', $userXp)
                ->last();

            // Next level
            $nextLevel = $levels
                ->where('required_xp', '>', $userXp)
                ->first();

            // Progress calculation
            $requiredXp = $nextLevel?->required_xp ?? $currentLevel?->required_xp ?? 0;

            $progressPercent = $requiredXp > 0
                ? round(($userXp / $requiredXp) * 100)
                : 100;

            $xpToNextLevel = max(0, $requiredXp - $userXp);

            /**
             *  STREAK INFO
             */
            $streakDays = helpers::calculateStreak($userId);
            $streakXpPerDay = 5; // configurable
            $streakBonusXp = $streakDays * $streakXpPerDay;

            // custom data
            $data = [
                'current_progress' => [
                    'current_xp'        => $userXp,
                    'current_level'     => $currentLevel ? [
                        'id'        => $currentLevel->id,
                        'name'      => $currentLevel->name,
                        'daily_roi' => $currentLevel->daily_roi,
                        'icon'      => asset($currentLevel->icon),
                    ] : null,

                    'next_level' => $nextLevel ? [
                        'id'          => $nextLevel->id,
                        'name'        => $nextLevel->name,
                        'required_xp' => $nextLevel->required_xp,
                    ] : null,

                    'required_xp'       => $requiredXp,
                    'progress_percent'  => min($progressPercent, 100),
                    'xp_to_next_level'  => $xpToNextLevel,
                    //  NEW STREAK STACK
                    'streak' => [
                        'days'        => $streakDays,
                        'bonus_xp'    => $streakBonusXp,
                        'xp_per_day'  => $streakXpPerDay,
                    ],
                ],
            ];

            $message = translateHelper::translateCached(
                'current_progress_retrieved_success',
                'Current progress retrieved successfully.',
                $lang
            );

            return $this->sendResponse($data, $message);
        }catch (\Exception $e){
            Log::error('Failed to retrieve current progress', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            $message = translateHelper::translateCached(
                'current_progress_retrieved_failed',
                'Failed to retrieve current progress. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }
}
