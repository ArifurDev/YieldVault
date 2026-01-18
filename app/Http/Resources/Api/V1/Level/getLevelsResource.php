<?php

namespace App\Http\Resources\Api\V1\Level;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getLevelsResource extends JsonResource
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
        return [
            'id'              => $this->id,
            'name'            => $this->name ?  translateHelper::translateCached(
                'task_title_' . md5($this->id),
                $this->name,
                $lang
            ) : null,
            'slug'            => $this->slug,
            'icon'            => $this->icon
                ? asset($this->icon)
                : asset('backend/assets/media/icons/placeholder.svg'),

            'required_xp'     => (int) $this->required_xp,
            'daily_roi'       => (float) $this->daily_roi,

            'order'           => (int) $this->order,

            'is_coming_soon'  => (bool) $this->is_coming_soon,
        ];
    }
}
