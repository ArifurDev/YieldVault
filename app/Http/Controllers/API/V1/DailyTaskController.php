<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Task\CompletedTaskRequest;
use App\Http\Resources\Api\V1\Task\getTodayTaskResource;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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

            $tasks = DailyTask::with(['task', 'logs' => function($q) {
                    $q->where('user_id', auth()->id());
                }])
                ->where('date', $today)
                ->where('is_active', 1)
                ->get();
            return $this->sendResponse(getTodayTaskResource::collection($tasks),"List of today tasks retrieved successfully.");
        }catch (\Exception $e){
            return $this->sendError("Failed to retrieved today task:".$e->getMessage(),[],500);
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
                return $this->sendError("Daily task not found",[], 404);
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
                return $this->sendError("You already completed this task.",[],400);
            }

            // Create log
            DailyTaskLog::create([
                'user_id'       => $user->id,
                'task_id'       => $dailyTask->task_id,
                'daily_task_id' => $dailyTask->id,
                'xp_rewarded'   => $dailyTask->task->xp,
            ]);

            return $this->sendResponse([],"Daily task completed.",200);
        }catch (\Exception $e){
            return $this->sendError("Failed to completed daily task:".$e->getMessage(),[],500);
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

            // XP user earned
            $completed_xp = $logs->sum('xp_rewarded');

            // Streak Calculation
            $completedDays = $logs->pluck('dailyTask.date')
                ->unique()
                ->map(fn($d) => $d->format('Y-m-d'))
                ->toArray();

            $streak = 0;
            $current = now()->format('Y-m-d');

            while (in_array($current, $completedDays)) {
                $streak++;
                $current = now()->subDays($streak)->format('Y-m-d');
            }

            // LIMIT HISTORY TO TODAY + LAST 6 DAYS ONLY
            $startDate = now()->subDays(6)->format('Y-m-d');
            $endDate   = now()->format('Y-m-d');

            // Build date list (latest first)
            $dateRange = [];
            for ($i = 0; $i < 7; $i++) {
                $dateRange[] = now()->subDays($i)->format('Y-m-d');
            }

            // History bucket
            $history = [];

            // Completed tasks ONLY for last 7 days
            $filteredLogs = $logs->filter(function ($log) use ($startDate, $endDate) {
                $d = $log->dailyTask->date->format('Y-m-d');
                return $d >= $startDate && $d <= $endDate;
            });

            // Completed tasks
            foreach ($filteredLogs as $log) {
                $date = $log->dailyTask->date->format('Y-m-d');
                $task = $log->task;

                $history[$date][] = [
                    'task_id'    => $task->id,
                    'task_title' => $task->title,
                    'xp'         => $task->xp,
                    'type'       => $task->type,
                    'status'     => 'completed',
                ];
            }

            // Pending tasks for each date
            foreach ($dateRange as $date) {

                // Fetch daily tasks for the date
                $dailyTasks = DailyTask::with('task')
                    ->where('date', $date)
                    ->get();

                if ($dailyTasks->isEmpty()) continue;

                foreach ($dailyTasks as $dt) {
                    $task = $dt->task;

                    // Completed already?
                    $completed = isset($history[$date])
                        ? collect($history[$date])->where('task_id', $task->id)->count()
                        : 0;

                    if ($completed === 0) {
                        $history[$date][] = [
                            'task_id'    => $task->id,
                            'task_title' => $task->title,
                            'xp'         => $task->xp,
                            'type'       => $task->type,
                            'status'     => 'pending',
                        ];
                    }
                }
            }

            // Sort by latest first
            krsort($history);

            // TOTAL XP OF ALL TASKS (all time)
            $total_xp = DailyTask::join('tasks', 'daily_tasks.task_id', '=', 'tasks.id')
                ->sum('tasks.xp');

            // FINAL RESPONSE
            $response = (object) [
                'total_xp'     => (int) $total_xp,
                'completed_xp' => (int) $completed_xp,
                'streak'       => $streak,
                'history'      => $history,
            ];

            return $this->sendResponse($response, "Tasks history retrieved successfully.");
        }catch (\Exception $e){
            Log::error("Failed to retrieved daily tasks history:".$e->getMessage(),[]);
            return $this->sendError("Failed to retrieved daily tasks history.",[],500);
        }
    }
}
