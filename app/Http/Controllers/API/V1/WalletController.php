<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use ApiResponse;

    /**
     * My wallet
     */
    public function getWallet()
    {
        try {
            $user = auth()->user();

            $wallet = $user->wallet;
            $manager = $user->manager;

            // custom response
            $data = [
                'balance' => number_format($wallet?->balance ?? 0, 2),
                'last_updated' => $wallet?->updated_at
                    ? $wallet->updated_at->format('M d, Y \a\t H:i')
                    : null,
                'manager' => $manager ? [
                    'id' => $manager->id,
                    'first_name' => $manager->first_name,
                    'last_name' => $manager->last_name,

                    // ✅ correct column name
                    'account_number' => $manager->manager_account,

                    // ✅ generate manager assigned id (MNG-101)
                    'manager_accsgn_id' => 'MNG-' . str_pad($manager->id, 3, '0', STR_PAD_LEFT),
                ] : null,
            ];

            return $this->sendResponse($data, 'Wallet retrieved successfully');

        } catch (\Throwable $e) {
            return $this->sendError( 'Failed to retrieve wallet information.',[], 500);
        }
    }

}
