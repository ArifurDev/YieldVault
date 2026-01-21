<?php

namespace App\Http\Resources\Api\V1\Wallet;

use App\Helpers\translateHelper;
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
        $lang = $request->header('Accept-Language', 'en');

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
                    'name' => $this->translateUserName($this->fromUser, $lang),
                    'role' => $this->fromUser->role,
                ]
                : null,

            'to' => $this->toUser ? [
                    'id'   => $this->toUser->id,
                    'name' => $this->translateUserName($this->toUser, $lang),
                    'role' => $this->toUser->role,
                ]
                : null,

            'datetime' => $this->created_at->toDateTimeString(),
        ];
    }

    protected function translateUserName($user, string $lang): string
    {
        if ($lang === 'en') {
            return trim($user->first_name . ' ' . $user->last_name);
        }

        $firstName = translateHelper::translateCached(
            'value_first_name_' . $user->id,
            $user->first_name,
            $lang
        );

        $lastName = translateHelper::translateCached(
            'value_last_name_' . $user->id,
            $user->last_name,
            $lang
        );

        return trim($firstName . ' ' . $lastName);
    }


    /**
     * Build transaction note
     */
    protected function buildNote(int $authUserId): string
    {
//        // Admin fund
//        if ($this->source === 'admin_fund') {
//            return $this->type === 'credit'
//                ? 'Admin: Fund added'
//                : 'Admin: Fund deducted';
//        }
//
//        // Deposit
//        if ($this->source === 'deposit') {
//            return $this->type === 'credit'
//                ? 'Deposit received'
//                : 'Deposit sent';
//        }
//
//        // Withdraw
//        if ($this->source === 'withdraw') {
//            return $this->type === 'debit'
//                ? 'Withdraw request approved'
//                : 'Withdraw reversed';
//        }
//
//        // Fallback
//        return ucfirst($this->source).' transaction';
        $lang = request()->header('Accept-Language', 'en');
        // Admin fund
        if ($this->source === 'admin_fund') {
            return translateHelper::translateCached(
                $this->type === 'credit'
                    ? 'wallet_note_admin_fund_added'
                    : 'wallet_note_admin_fund_deducted',
                $this->type === 'credit'
                    ? 'Admin added funds to your wallet.'
                    : 'Admin deducted funds from your wallet.',
                $lang
            );
        }

        // Deposit
        if ($this->source === 'deposit') {
            return translateHelper::translateCached(
                $this->type === 'credit'
                    ? 'wallet_note_deposit_received'
                    : 'wallet_note_deposit_sent',
                $this->type === 'credit'
                    ? 'Deposit received successfully.'
                    : 'Deposit sent.',
                $lang
            );
        }

        // Withdraw
        if ($this->source === 'withdraw') {
            return translateHelper::translateCached(
                $this->type === 'debit'
                    ? 'wallet_note_withdraw_approved'
                    : 'wallet_note_withdraw_reversed',
                $this->type === 'debit'
                    ? 'Withdraw request approved.'
                    : 'Withdraw reversed.',
                $lang
            );
        }

        // Generic fallback
        return translateHelper::translateCached(
            'wallet_note_generic_' . $this->source,
            ucfirst(str_replace('_', ' ', $this->source)) . ' transaction.',
            $lang
        );
    }
}
