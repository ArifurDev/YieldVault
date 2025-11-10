<?php

namespace App\Http\Resources\Api\V1\Firebase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FirebaseResource extends JsonResource
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
            'id'        => $this->id,
            'user_id'   => $this->user_id,
            'token'     => $this->token,
            'device_id' => $this->device_id,
            'is_active' => $this->is_active,
        ];
    }
}
