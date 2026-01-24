<?php

namespace App\Http\Resources\Api\V1\SupportTicket;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportMessageResource extends JsonResource
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
            'message'   => $this->message,
            'is_read'   => (bool) $this->is_read,
            'created_at'=> $this->created_at->diffForHumans(),

            'sender' => [
                'id'     => $this->sender->id,
                'name'   => $this->sender->first_name.' '.$this->sender->last_name,
                'avatar' => $this->sender->avatar,
                'role'   => $this->sender->role->value ?? null,
            ],

            // 👇 replies under this comment
            'replies' => SupportReplyResource::collection(
                $this->whenLoaded('replies')
            ),
        ];
    }
}
