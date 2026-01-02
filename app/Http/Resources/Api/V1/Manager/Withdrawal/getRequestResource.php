<?php

namespace App\Http\Resources\Api\V1\Manager\Withdrawal;

use App\Helpers\helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getRequestResource extends JsonResource
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
            // Request info
            'id'          => $this->id,
            'request_id'  => 'WDR-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),
            'amount'      => number_format($this->amount, 2),
            'status'      => $this->status,
            'date'        => $this->created_at->format('M d, Y'),

            // User info
            'user' => [
                'id'            => $this->user->id,
                'name'          => trim($this->user->first_name . ' ' . $this->user->last_name),
                'avatar'        => $this->user->avatar
                    ? helpers::generateTempURL($this->user->avatar,config('app.file_system'))
                    : null,
            ],
        ];
    }
}
