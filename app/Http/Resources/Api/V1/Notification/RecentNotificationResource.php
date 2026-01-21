<?php

namespace App\Http\Resources\Api\V1\Notification;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentNotificationResource extends JsonResource
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

        $payload = $this->data ?? [];

        // Safe extraction (field)
        $title   = $payload['title']   ?? null;
        $message = $payload['message'] ?? null;

        // Translate ONLY title & message
        if ($title) {
            $payload['title'] = translateHelper::translateCached(
                'notification_title_' . md5($title),
                $title,
                $lang
            );
        }

        if ($message) {
            $payload['message'] = translateHelper::translateCached(
                'notification_message_' . md5($message),
                $message,
                $lang
            );
        }

        return [
            'id'         => $this->id,
            'data'       => $payload,
            'read_at'    => $this->read_at,
            'created_at' => $this->created_at,
        ];
    }
}
