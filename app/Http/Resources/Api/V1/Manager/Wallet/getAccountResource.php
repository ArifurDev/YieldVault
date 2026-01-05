<?php

namespace App\Http\Resources\Api\V1\Manager\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getAccountResource extends JsonResource
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
            'id'             => $this->id,
            'type'           => $this->type->value,
            'label'          => $this->label,
            'account_number' => $this->account_number,
            'network'        => $this->network,
            'is_active'      => $this->is_active ? 'Active' : 'Inactive',
        ];
    }
}
