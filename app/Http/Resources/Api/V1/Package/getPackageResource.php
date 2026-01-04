<?php

namespace App\Http\Resources\Api\V1\Package;

use App\Models\DailyTaskLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class getPackageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        // Default XP (later replace with auth()->user()->xp)
        $earnedXp   = DailyTaskLog::where('user_id',auth()->user()->id)->sum('xp_rewarded');
        $userXp     = $earnedXp ?? 0;
        $requiredXp = $this?->level?->required_xp ?? 0;
        $isUnlocked = $userXp >= $requiredXp;

//        return [
//            'id'   => $this->id,
//            'name' => $this->name,
//
//            // Investment info
//            'investment'    => (float) $this->price,
//            'duration_days' => (int) $this->duration_days,
//
//            // ROI
//            'roi_per_day' => (float) $this->level->daily_roi,
//            'roi_text'    => '$' . number_format(
//                    ($this->price * $this->level->daily_roi) / 100,
//                    2
//                ),
//
//            // LOCK / UNLOCK STATUS
//            'is_unlocked' => $isUnlocked,
//            'status' => $isUnlocked ? 'unlocked' : 'locked',
//
//            // CTA helper
//            'cta' => $isUnlocked ? 'invest_now' : 'level_up',
//
//            // Level info
//            'level' => [
//                'id' => $this->level->id,
//                'name' => $this->level->name,
//                'slug' => $this->level->slug ? ucfirst(Str::before($this->level->slug, '-')) : null,
//                'required_xp' => (int) $requiredXp,
//            ],
//
//            // Lock reason (for UI)
//            'lock_reason' => $isUnlocked
//                ? null
//                : "Unlock at {$this->level->name}",
//        ];

        return [
            'id'   => $this->id,
            'name' => $this->name,

            // Investment info
            'investment'    => number_format($this->price, 2),
            'duration_days' => (int) $this->duration_days,

            // ROI (ALWAYS 2 decimal places)
            'roi_per_day' => number_format($this->level->daily_roi, 2),
            'roi_text'    => '$' . number_format(
                    ($this->price * $this->level->daily_roi) / 100,
                    2
                ),

            // LOCK / UNLOCK STATUS
            'is_unlocked' => $isUnlocked,
            'status'      => $isUnlocked ? 'unlocked' : 'locked',

            // CTA helper
            'cta' => $isUnlocked ? 'invest_now' : 'level_up',

            // Level info
            'level' => [
                'id'          => $this->level->id,
                'name'        => $this->level->name,
                'slug'        => $this->level->slug
                    ? ucfirst(Str::before($this->level->slug, '-'))
                    : null,
                'required_xp' => (int) $requiredXp,
            ],

            // Lock reason (for UI)
            'lock_reason' => $isUnlocked
                ? null
                : "Unlock at {$this->level->name}",
        ];
        
    }
}
