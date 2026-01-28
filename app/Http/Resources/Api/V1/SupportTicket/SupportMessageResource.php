<?php

namespace App\Http\Resources\Api\V1\SupportTicket;

use App\Helpers\translateHelper;
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
        $lang = $request->header('Accept-Language', 'en');
//        return [
//            'id'        => $this->id,
//            'message'   => $this->message ? translateHelper::translateCached(
//                'support_ticket_message_' . md5($this->id),
//                $this->message,
//                $lang)
//                :  null,
//            'is_read'   => (bool) $this->is_read,
//            'created_at'=> $this->created_at->diffForHumans(),
//
//            'sender' => [
//                'id'     => $this->sender->id,
//                'name'   => $this->sender ? translateHelper::translateUserName($this->sender,$lang) : null,
//                'avatar' => $this->sender->avatar,
//                'role'   => $this->sender->role->value ?? null,
//            ],
//
//            // 👇 replies under this comment
//            'replies' => SupportMessageResource::collection(
//                $this->whenLoaded('replies')
//            ),
//        ];


        $isReply = !is_null($this->reply_to_id);


        //  translate current message
        $translatedMessage = $this->message
            ? translateHelper::translateCached(
                'support_ticket_message_' . md5($this->id),
                $this->message,
                $lang
            )
            : null;

        //  translate parent message (if reply)
        $translatedParentMessage = $isReply && $this->parent
            ? translateHelper::translateCached(
                'support_ticket_message_' . md5($this->parent->id),
                $this->parent->message,
                $lang
            )
            : null;

        return [
            'id' => $this->id,

            // EXACT format you asked for
            'text' => $isReply
                ? '"' . $translatedParentMessage . '" → ' . $translatedMessage
                : $translatedMessage,

            'is_reply' => $isReply,
            'is_read'   => (bool) $this->is_read,

            'created_at' => $this->created_at->diffForHumans(),

            'sender' => [
                'id'   => $this->sender->id,
                'name' => $this->sender ? translateHelper::translateUserName($this->sender,$lang) : null,
                'role' => $this->sender->role->value ?? null,
            ],
        ];
    }
}
