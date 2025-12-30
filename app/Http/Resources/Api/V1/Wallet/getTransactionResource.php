<?php

namespace App\Http\Resources\Api\V1\Wallet;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class getTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        $authUser = $request->user();

        $isDebit = $this->from_user_id === $authUser->id;
        $flow    = $isDebit ? 'debit' : 'credit';

        return [
            'id'     => $this->id,
            'amount' => number_format($this->amount, 2),
            'flow'   => $flow,
            'type'   => $this->type,
            'source' => $this->source,
            'status' => $this->status,

            // Human-readable note
            'note'   => $this->buildNote($authUser->id),

            'from'   => $this->fromUser ? [
                    'id'   => $this->fromUser->id,
                    'name' => $this->fromUser->first_name.' '.$this->fromUser->last_name,
                    'role' => $this->fromUser->role,
                ]
                : null,

            'to' => $this->toUser ? [
                    'id'   => $this->toUser->id,
                    'name' => $this->toUser->first_name.' '.$this->toUser->last_name,
                    'role' => $this->toUser->role,
                ]
                : null,

            'datetime' => $this->created_at->toDateTimeString(),
        ];
    }

    /**
     * Build transaction note
     */
    protected function buildNote(int $authUserId): string
    {
        // Admin fund
        if ($this->source === 'admin_fund') {
            return $this->type === 'credit'
                ? 'Admin: Fund added'
                : 'Admin: Fund deducted';
        }

        // Deposit
        if ($this->source === 'deposit') {
            return $this->type === 'credit'
                ? 'Deposit received'
                : 'Deposit sent';
        }

        // Withdraw
        if ($this->source === 'withdraw') {
            return $this->type === 'debit'
                ? 'Withdraw request approved'
                : 'Withdraw reversed';
        }

        // Fallback
        return ucfirst($this->source).' transaction';
    }
}
