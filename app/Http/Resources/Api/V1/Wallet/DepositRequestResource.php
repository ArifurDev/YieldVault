<?php

namespace App\Http\Resources\Api\V1\Wallet;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositRequestResource extends JsonResource
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
            'id' => $this->id,
            'amount' => number_format($this->amount, 2),
            'status' => $this->status,
            'remarks' => $this->remarks ,
            'proof_url' => $this->proof_path
                ? helpers::generateTempURL($this->proof_path,config('app.file_system'))
                : null,
            'requested_at' => $this->created_at->toDateTimeString(),
            // Helpful metadata
            'manager' => [
                'id' => $this->manager_id,
                'code' => 'MNG-' . str_pad($this->manager_id, 3, '0', STR_PAD_LEFT),
            ],
        ];
    }
}
