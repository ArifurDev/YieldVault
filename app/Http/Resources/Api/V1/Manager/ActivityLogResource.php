<?php

namespace App\Http\Resources\Api\V1\Manager;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ActivityLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $lang = $request->header('Accept-Language', 'en');
        // Set Carbon locale for human readable time
        Carbon::setLocale(explode(',', $request->header('Accept-Language', 'en'))[0]);
        return [
            'id' => $this->id,

            // TYPE & ACTION
            'type'   => $this->type,      // deposit | withdraw | kyc | login | register
            'action' => $this->action,    // approved | rejected | paid | flagged

            // MESSAGE
            'message' => $this->message ? translateHelper::translateCached(
                'activity_log_message_' . md5($this->id),
                $this->message,
                $lang
            ) : null,
            'level'   => $this->level, // success | danger | warning | info

            // TIMESTAMPS
            'created_at' => [
                'raw'   => $this->created_at?->toDateTimeString(),
                'human' => $this->created_at?->diffForHumans(),
            ],
        ];
    }
}
