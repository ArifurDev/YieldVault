<?php

namespace App\Http\Resources\Api\V1\Profile;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UpdateResource extends JsonResource
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
            'id'         => $this->id,
            'uuid'       => $this->uuid,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'avatar'     => $this->avatar ? Helpers::generateTempURL($this->avatar,config('app.file_system')) : null,
            'role'       => $this->role,
            'bio'        => $this->bio,
        ];
    }
}
