<?php

namespace App\Http\Resources\Api\V1\Profile;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
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

        return [
            'id'         => $this->id,
            'first_name' => $this->first_name ? $first_name : $this->first_name,
            'last_name'  => $this->last_name ? $last_name : $this->last_name,
            'email'      => $this->email,
            'avatar'     => $this->avatar ? Helpers::generateTempURL($this->avatar,config('app.file_system')) : null,
            'role'       => $this->role,
        ];
    }
}
