<?php

namespace App\Http\Controllers\API\V1;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\CompletedTaskRequest;
use App\Http\Resources\Api\V1\Task\getTodayTaskResource;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DailyTaskController extends Controller
{
    use ApiResponse;

    /**
     * list of today task
     */
    public function todayTask()
    {
        try {
            // get current data
            $today = now()->format('Y-m-d');
            $tasks = DailyTask::with(['task', 'logs' => function ($q) {
                $q->where('user_id', auth()->id());
            }])
                ->where('date', $today)
                ->where('is_active', 1)
                ->get();

            return $this->sendResponse(getTodayTaskResource::collection($tasks), "List of today tasks retrieved successfully.");
        } catch (\Exception $e) {
            return $this->sendError("Failed to retrieved today task:" . $e->getMessage(), [], 500);
        }
    }

    /*
     * today task completed
     */
    public function taskCompleted(CompletedTaskRequest $request)
    {
        $validated = $request->validated();

        try {
             //get current auth
             $user = auth()->user();

             // get current data
             $today = now()->format('Y-m-d');

             // find daily task
             $dailyTask = DailyTask::with('task')->find($validated['daily_task_id']);
             // check if not found
             if (!$dailyTask) {
                 return $this->sendError("Daily task not found", [], 404);
             }

             // check if task is for today
             if ($dailyTask->date->format('Y-m-d') !== $today) {
                 return $this->sendError("You can only complete today's task.", [], 400);
             }

             // prevent double completion
             $alreadyDone = DailyTaskLog::where('daily_task_id', $dailyTask->id)
                 ->where('user_id', $user->id)
                 ->exists();
             if ($alreadyDone) {
                 return $this->sendError("You already completed this task.", [], 400);
             } else {
             // Create log
             DailyTaskLog::create([
               'user_id'       => $user->id,
               'task_id'       => $dailyTask->task_id,
               'daily_task_id' => $dailyTask->id,
               'xp_rewarded'   => $dailyTask->task->xp,
             ]);
                 return $this->sendResponse([], "Daily task completed.", 200);
             }
        } catch (\Exception $e) {
            return $this->sendError("Failed to completed daily task:" . $e->getMessage(), [], 500);
        }
    }

    /**
     * Task History (XP + Streak + Completed + Pending)
     */
    public function historyTask()
    {
        try {
            $userId = auth()->id();

            // Load ALL completed logs for XP + streak
            $logs = DailyTaskLog::with(['dailyTask.task'])
                ->where('user_id', $userId)
                ->get();

            /**
             * remove broken logs (dailyTask or date missing)
             */
            $logs = $logs->filter(function ($log) {
                return $log->dailyTask && $log->dailyTask->date;
            });

            // XP user earned
            $completed_xp = $logs->sum('xp_rewarded');

            /*
             * STREAK CALCULATION
             */
            $completedDays = $logs
                ->map(fn($log) => $log->dailyTask->date?->format('Y-m-d'))
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $streak = 0;
            $current = now()->format('Y-m-d');

            while (in_array($current, $completedDays)) {
                $streak++;
                $current = now()->subDays($streak)->format('Y-m-d');
            }

            /**
             * DATE RANGE (TODAY + LAST 6 DAYS)
             */
            $dateRange = [];
            for ($i = 0; $i < 7; $i++) {
                $dateRange[] = now()->subDays($i)->format('Y-m-d');
            }

            // History bucket
            $history = [];

            /**
             * COMPLETED TASKS
             */
            foreach ($logs as $log) {
                $date = $log->dailyTask->date?->format('Y-m-d');
                if (!$date || !in_array($date, $dateRange)) {
                    continue;
                }

                $task = $log->task;

                $history[$date][] = [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'xp' => $task->xp,
                    'type' => $task->type,
                    'status' => 'completed',
                ];
            }

            /**
             * PENDING TASKS
             */
            foreach ($dateRange as $date) {

                $dailyTasks = DailyTask::with('task')
                    ->where('date', $date)
                    ->get();

                if ($dailyTasks->isEmpty()) {
                    continue;
                }

                foreach ($dailyTasks as $dt) {
                    if (!$dt->task) continue;

                    $task = $dt->task;

                    $alreadyCompleted = isset($history[$date])
                        ? collect($history[$date])->where('task_id', $task->id)->count()
                        : 0;

                    if ($alreadyCompleted === 0) {
                        $history[$date][] = [
                            'task_id' => $task->id,
                            'task_title' => $task->title,
                            'xp' => $task->xp,
                            'type' => $task->type,
                            'status' => 'pending',
                        ];
                    }
                }
            }

            // Sort latest date first
            krsort($history);

            /**
             * TOTAL XP (ALL TASKS)
             */
            $total_xp = DailyTask::join('tasks', 'daily_tasks.task_id', '=', 'tasks.id')
                ->sum('tasks.xp');

            $response = (object)[
                'total_xp' => (int)$total_xp,
                'completed_xp' => (int)$completed_xp,
                'streak' => $streak,
                'history' => $history,
            ];

            return $this->sendResponse($response, "Tasks history retrieved successfully.");

        } catch (\Exception $e) {
            Log::error("Failed to retrieve daily tasks history: " . $e->getMessage());
            return $this->sendError("Failed to retrieve daily tasks history.", [], 500);
        }
    }

}
