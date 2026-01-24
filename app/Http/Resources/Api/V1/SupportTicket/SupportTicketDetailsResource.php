<?php

namespace App\Http\Resources\Api\V1\SupportTicket;

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
        return [
            'ticket_id' => $this->id,
            'ticket_no' => '#'.str_pad($this->id, 6, '0', STR_PAD_LEFT),

            'subject'  => $this->subject,
            'category' => $this->category->value,
            'priority' => $this->priority->value,
            'status'   => $this->status->value,

            'created_at' => $this->created_at->format('d M Y, h:i A'),
            'updated_at' => $this->updated_at->diffForHumans(),

            // 👇 Messages
            'messages' => SupportMessageResource::collection(
                $this->whenLoaded('messages')
            ),
        ];
    }
}
