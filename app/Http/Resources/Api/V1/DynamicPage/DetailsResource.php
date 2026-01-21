<?php

namespace App\Http\Resources\Api\V1\DynamicPage;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');
//        return parent::toArray($request);

        return [
            'title' => translateHelper::translateCached(
                'dynamic_page_title_' . md5($this->slug),
                $this->title ?? '',
                $lang
            ),

            'content' => translateHelper::translateCached(
                'dynamic_page_content_' . md5($this->slug),
                $this->content ?? '',
                $lang
            ),

            'last_updated' => $this->updated_at,
        ];
    }
}
