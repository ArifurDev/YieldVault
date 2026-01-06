<?php

namespace App\Http\Resources\Api\V1\Manager;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,

            // TYPE & ACTION
            'type'   => $this->type,      // deposit | withdraw | kyc | login | register
            'action' => $this->action,    // approved | rejected | paid | flagged

            // MESSAGE
            'message' => $this->message,
            'level'   => $this->level, // success | danger | warning | info

            // TIMESTAMPS
            'created_at' => [
                'raw'   => $this->created_at?->toDateTimeString(),
                'human' => $this->created_at?->diffForHumans(),
            ],
        ];
    }
}
