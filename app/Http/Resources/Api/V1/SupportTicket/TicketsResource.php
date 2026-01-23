<?php

namespace App\Http\Resources\Api\V1\SupportTicket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketsResource extends JsonResource
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
            'id'          => $this->id,
            'support_id'  => '#' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'subject'     => $this->subject,
            'category'    => $this->category,
            'priority'    => $this->priority,
            'status'      => $this->status,
            'assigned_to' => $this->assignedTo
                ? [
                    'id'         => $this->assignedTo->id,
                    'first_name' => $this->assignedTo->first_name,
                    'last_name'  => $this->assignedTo->last_name,
                ]
                : null,
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
