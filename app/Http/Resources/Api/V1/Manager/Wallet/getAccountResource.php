<?php

namespace App\Http\Resources\Api\V1\Manager\Wallet;

use App\Helpers\translateHelper;
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
        $lang = $request->header('Accept-Language', 'en');
//        return parent::toArray($request);
        return [
            'id'             => $this->id,
            'type'           => $this->type->value,
            'label'          => $this->label ? translateHelper::translateCached(
                'wallet_account_label_' . md5($this->label),
                $this->label,
                $lang
            ) : null,
            'account_number' => $this->account_number,
            'network'        => $this->network,
            'is_active'      => $this->is_active ? 'Active' : 'Inactive',
        ];
    }
}
