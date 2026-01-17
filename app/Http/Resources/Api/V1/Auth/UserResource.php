<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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

//        return parent::toArray($request);
        return [
            'id'            => $this->id,
            'first_name'    => $this->first_name ? $first_name : $this->first_name,
            'last_name'     => $this->last_name ? $last_name : $this->last_name,
            'email'         => $this->email,
            'role'          => $this->role,
            'kyc_status'    => $this->kyc_status ?? null
        ];
    }
}
