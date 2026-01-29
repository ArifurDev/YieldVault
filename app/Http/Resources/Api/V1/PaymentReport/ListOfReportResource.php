<?php

namespace App\Http\Resources\Api\V1\PaymentReport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListOfReportResource extends JsonResource
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
            'id'         => $this->id,
            'report_id'  => 'PR-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'ticket_no'  => '#' . str_pad($this->ticket_id, 6, '0', STR_PAD_LEFT),
            'category'   => $this->category?->label($lang),
            'issue_type' => $this->issue_type?->label($lang),
            'amount'     => $this->transaction
                ? number_format($this->transaction->amount, 2)
                : null,
            'status'     => $this->status?->label($lang),
            'created_at' => $this->created_at->format('d M Y, h:i A'),
        ];
    }
}
