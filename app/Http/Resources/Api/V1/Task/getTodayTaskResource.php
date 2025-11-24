<?php

namespace App\Http\Resources\Api\V1\Task;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getTodayTaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $task = $this->task;

        // check if user completed today's task
        $userCompleted = $this->logs
            ->where('user_id', auth()->id())
            ->isNotEmpty();

        return [
            'id'         => $this->id,
            'date'       => $this->date?->format('Y-m-d'),
            'sort_order' => $this->sort_order,
            'is_active'  => (bool) $this->is_active,

            'task' => $task ? [
                'id'         => $task->id,
                'title'      => $task->title,
                'type'       => $task->type->value,
                'xp'         => $task->xp,
                'duration'   => $task->duration,
                'media_type' => $task->media_type,
                'media_path' => $this->generateMediaUrl($task),
            ] : null,
            'is_complete' => $userCompleted,
        ];
    }

    private function generateMediaUrl($task)
    {
        if (!$task) return null;

        if (in_array($task->media_type, ['image','video'])) {
            return $task->media_path
                ? \App\Helpers\helpers::generateTempURL($task->media_path, config('app.file_system'))
                : null;
        }

        if ($task->media_type === 'url') {
            return $task->media_path;
        }

        return null;
    }

}
