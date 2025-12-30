<?php

namespace App\Http\Resources\Api\V1\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawRequestResource extends JsonResource
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
            'id'      => $this->id,
            'amount'  => number_format($this->amount, 2),
            'status'  => $this->status,
            'remarks' => $this->remarks,
            'requested_at' => $this->created_at->toDateTimeString(),
            'manager'  => [
                'id'   => $this->manager_id,
                'code' => 'MNG-' . str_pad($this->manager_id, 3, '0', STR_PAD_LEFT),
            ],
        ];
    }
}
