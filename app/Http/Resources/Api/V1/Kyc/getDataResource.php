<?php

namespace App\Http\Resources\Api\V1\Kyc;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $user = $this->user ?? null;
        return [
            'id'               => $this->id,
            'document_type'    => $this->document_type,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at'       => $this->created_at,
            'user' => $user ? [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'kyc_status' => $user->kyc_status,
            ] : null,
        ];
    }
}
