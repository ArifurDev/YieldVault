<?php

namespace App\Http\Resources\Api\V1\Manager\Withdrawal;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getRequestDetailsResource extends JsonResource
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
            /* ================= Request Info ================= */
            'id'         => $this->id,
            'request_id' => 'WDR-' . str_pad($this->id, 6, '0', STR_PAD_LEFT),

            'amount'  => number_format($this->amount, 2),
            'status'  => $this->status,
            'remarks' => $this->remarks ? translateHelper::translateCached(
                'withdraw_remarks_' . md5($this->remarks),
                $this->remarks,
                $lang
            ) : null,

            'date_&_time' => [
                'date' => $this->created_at->format('M d, Y'),
                'time' => $this->created_at->format('h:i A'),
            ],

            /* ================= User Info ================= */
            'user' => [
                'id'     => $this->user->id,
                'name'   => translateHelper::translateUserName($this->user,$lang),
                'avatar' => $this->user->avatar
                    ? helpers::generateTempURL(
                        $this->user->avatar,
                        config('app.file_system')
                    )
                    : null,
            ],

            /* ================= Proof ================= */
            'proof_of_payment' => $this->proof_path
                ? helpers::generateTempURL(
                    $this->proof_path,
                    config('app.file_system')
                )
                : null,

            /* ================= Timeline ================= */
            'activity_log' => [
                [
                    'label' => translateHelper::translateCached(
                        'Withdrawal_activity_requested',
                        'Withdrawal Requested',
                        $lang
                    ),
                    'value' => $this->created_at->format('M d, Y h:i A'),
                ],
            ],

            /* ================= UI Helpers ================= */
            'actions' => [
                'can_mark_as_paid' => $this->status === 'approved',
                'can_approve'      => $this->status === 'pending',
                'can_reject'       => $this->status === 'pending',
            ],
        ];
    }
}
