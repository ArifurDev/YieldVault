<?php

namespace App\Http\Resources\Api\V1\Kyc;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class showDataResource extends JsonResource
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
            'front-side'       => $this->file_side_1_path ? helpers::generateTempURL($this->file_side_1_path,config('app.file_system')) : null,
            'back-side'        => $this->file_side_2_path ? helpers::generateTempURL($this->file_side_2_path,config('app.file_system')) : null,
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
