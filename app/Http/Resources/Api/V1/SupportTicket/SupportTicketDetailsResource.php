<?php

namespace App\Http\Resources\Api\V1\SupportTicket;

use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('Accept-Language', 'en');

        return [
            'ticket_id' => $this->id,
            'ticket_no' => '#'.str_pad($this->id, 6, '0', STR_PAD_LEFT),

            'subject'     => $this->subject ? translateHelper::translateCached(
                'support_ticket_subject_' . md5($this->id),$this->subject,$lang)
                : null,
            'category'    => $this->category ? translateHelper::translateCached(
                'support_ticket_category_' . $this->category->value,$this->category->value,$lang)
                : null,
            'priority'    => $this->priority ? translateHelper::translateCached(
                    'support_ticket_priority_' . $this->priority->value,$this->priority->value,$lang)
                : null,
            'status'      => $this->status ? translateHelper::translateCached(
                    'support_ticket_status_' . $this->status->value,$this->status->value, $lang)
                : null,

            'created_at' => $this->created_at->format('d M Y, h:i A'),
            'updated_at' => $this->updated_at->diffForHumans(),

            // 👇 Messages
            'messages' => SupportMessageResource::collection(
                $this->whenLoaded('messages')
            ),
        ];
    }
}
