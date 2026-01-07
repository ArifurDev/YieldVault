<?php

namespace App\Http\Resources\Api\V1\Manager;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecentTransactionResource extends JsonResource
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
            'id'   => $this->source_id,
            'date' => $this->date
                ? \Carbon\Carbon::parse($this->date)->format('Y-m-d H:i:s')
                : null,

            'user'   => $this->user,
            'type'   => ucfirst($this->type), // Deposit / Withdraw
            'amount' => (float) $this->amount,
            'status' => ucfirst($this->status),
        ];
    }
}
