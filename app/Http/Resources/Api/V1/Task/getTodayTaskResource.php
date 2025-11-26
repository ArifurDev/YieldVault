<?php

namespace App\Http\Resources\Api\V1\Task;

use App\Models\ReferralLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
                'referral_link' => $task->type->value == "invite" ? $this->generateReferralLink() : null,
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

    /**
     * Generate or return existing valid referral link
     */
    private function generateReferralLink()
    {
        $user = auth()->user();

        // Check if a non-expired link already exists
        $existing = ReferralLink::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if ($existing) {
            return "https://wolfmarketin.softvencefsd.xyz/sing-up?ref=" . $existing->unique_token;
        }

        // Create NEW referral link (valid for 24 hours)
        $link = ReferralLink::create([
            'user_id'      => $user->id,
            'ref_code'     => strtoupper(Str::random(8)),
            'unique_token' => (string) Str::uuid(),
            'expires_at'   => now()->addHours(24),
        ]);

        return "https://wolfmarketin.softvencefsd.xyz/sing-up?ref=" . $link->unique_token;
    }



}
