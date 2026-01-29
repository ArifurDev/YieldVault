<?php

namespace App\Http\Resources\Api\V1\PaymentReport;

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
//        return parent::toArray($request);
        $lang = $request->header('Accept-Language', 'en');

        return [
            'id'        => $this->id,
            'report_id' => 'PR-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'ticket_no' => '#' . str_pad($this->ticket_id, 6, '0', STR_PAD_LEFT),

            'category' => [
                'key'   => $this->category?->value,
                'label' => $this->category?->label($lang),
            ],

            'issue_type' => [
                'key'   => $this->issue_type?->value,
                'label' => $this->issue_type?->label($lang),
            ],

            'status' => [
                'key'   => $this->status?->value,
                'label' => $this->status?->label($lang),
            ],

            'amount' =>  $this->transaction
                    ? number_format($this->transaction->amount, 2)
                    : null,

            'message' => $this->message
                ? translateHelper::translateCached(
                    'payment_report_message_' . md5($this->id),
                    $this->message,
                    $lang
                )
                : null,
            'resolved_at' => $this->resolved_at?->format('d M Y, h:i A'),
            'created_at'  => $this->created_at->format('d M Y, h:i A'),

            'user' => [
                'id'    => $this->user?->id,
                'name'  => translateHelper::translateUserName($this?->user,$lang),
                'email' => $this->user?->email,
            ],
        ];

    }
}
