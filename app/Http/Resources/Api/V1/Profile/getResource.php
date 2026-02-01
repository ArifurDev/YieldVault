<?php

namespace App\Http\Resources\Api\V1\Profile;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Models\DailyTaskLog;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class getResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');

        $firstNameKey = 'value_first_name_' . md5($this->id);
        $lastNameKey  = 'value_last_name_' . md5($this->id);

        $first_name = translateHelper::translateCached(
            $firstNameKey,
            $this->first_name,
            $lang
        );
        $last_name = translateHelper::translateCached(
            $lastNameKey,
            $this->last_name,
            $lang
        );


        // XP calculation
        $userXp = DailyTaskLog::where('user_id', $this->id)
            ->sum('xp_rewarded') ?? 0;

        // Current level
        $currentLevel = Level::where('is_active', true)
            ->where('required_xp', '<=', $userXp)
            ->orderByDesc('required_xp')
            ->first();

//        return parent::toArray($request);
        return [
            'id'            => $this->id,
            'first_name'    => $this->first_name ? $first_name : $this->first_name,
            'last_name'     => $this->last_name ? $last_name : $this->last_name,
            'email'         => $this->email,
            'avatar'        => $this->avatar ? Helpers::generateTempURL($this->avatar,config('app.file_system')) : null,
            'role'          => $this->role,
            'is_notify'     => (bool)$this->is_notify,
            'kyc_status'    => $this->kyc_status ?? null,
            //  XP + LEVEL (FINAL)
            'xp' => $userXp,

            'level' => $currentLevel ? [
                'id'   => $currentLevel->id,
                'name' => translateHelper::translateCached(
                    'level_name_' . md5($currentLevel->id),
                    $currentLevel->name,
                    $lang
                ),
            ] : [
                'id'   => 0,
                'name' => translateHelper::translateCached(
                    'level_starter',
                    'Starter',
                    $lang
                ),
            ],
        ];
    }
}
