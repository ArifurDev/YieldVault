<?php

namespace App\Http\Resources\Api\V1\Task;

use App\Helpers\helpers;
use App\Models\ReferralLink;
use App\Services\DataEncryptionService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        Log::info('Task media path', [
            'id' => $task->id,
            'media_type' => $task->media_type,
            'media_path' => $task->media_path,
        ]);

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
                'media_path' => $task->media_path ? $this->generateMediaUrl($task,config('app.file_system')) : null,
                'referral_link' => $task->type->value == "invite" ? $this->generateReferralLink($task->id) : null,
            ] : null,
            'is_complete' => $userCompleted,
        ];
    }

    private function generateMediaUrl($task)
    {
        if (!$task) return null;


        $mediaType = $task->media_type?->value;

        if (in_array($mediaType, ['image', 'video'])) {
            return helpers::generateTempURL($task->media_path,config('app.file_system'));
        }

        if ($task->media_type === 'url') {
            return $task->media_path;
        }

        return null;
    }

    /**
     * Generate or return existing valid referral link
     */
    private function generateReferralLink($taskId): string
    {
        $jwt = app(DataEncryptionService::class);
        $user = auth()->user();
        $taskEnc = $jwt->encrypt($taskId);

        // Check if a non-expired link already exists
        $existing = ReferralLink::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        // IF EXISTS → use existing
        if ($existing) {
            return helpers::getFrontendUrl()
                . '/user-auth/user-signup'
                . '?ref=' . $existing->unique_token
                . '&task=' . $taskEnc;
        }

        // ELSE create new
        $link = ReferralLink::create([
            'user_id'      => $user->id,
            'ref_code'     => strtoupper(Str::random(8)),
            'unique_token' => (string) Str::uuid(),
            'expires_at'   => now()->addHours(24),
        ]);

             return helpers::getFrontendUrl()
                . '/user-auth/user-signup'
                . '?ref=' . $link->unique_token
                . '&task=' . $taskEnc;
    }




}
