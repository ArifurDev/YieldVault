<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Manager\Wallet\StoreRequest;
use App\Http\Requests\Api\V1\Manager\Wallet\UpdateRequest;
use App\Http\Resources\Api\V1\Manager\Wallet\getAccountResource;
use App\Models\WalletAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletAccountController extends Controller
{
    use ApiResponse;

    /**
     * List wallet accounts
     */
    public function index()
    {
        try {
            // get current auth
            $user = auth()->user();
            if ($user->role->value !== Role::MANAGER->value)
            {
                return $this->sendError("You don't have permission to access this page",[],403);
            }

            $accounts = WalletAccount::where('user_id', $user->id)
                ->latest()
                ->get();

            return $this->sendResponse(getAccountResource::collection($accounts), 'Wallet accounts retrieved successfully');
        }catch (\Exception $e){
            Log::error("Failed to get accounts list",
            [
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);
            return $this->sendError("Failed to get accounts list",[],500);
        }
    }

    /**
     * Store wallet account
     */
    public function store(StoreRequest $request)
    {
        try {
            //get current auth
            $user = auth()->user();
            // get current auth
            $user = auth()->user();

            if ($user->role->value !== Role::MANAGER->value) {
                return $this->sendError("You don't have permission to access this page", [], 403);
            }

            $validated = $request->validated();

            DB::transaction(function () use ($validated, $user) {

                // Only one active account per type
                if ($validated['is_active']) {
                    WalletAccount::where('user_id', $user->id)
                        ->where('type', $validated['type'])
                        ->update(['is_active' => false]);
                }

                WalletAccount::create([
                    'user_id'        => $user->id,
                    'type'           => $validated['type'],
                    'label'          => $validated['label'],
                    'account_number' => $validated['account_number'],
                    'network'        => $validated['network'] ?? null,
                    'is_active'      => $validated['is_active'],
                ]);
            });

            return $this->sendResponse([], 'Wallet account added successfully');
        }catch (\Exception $e){
            Log::error("Failed to store account",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to store account",[],500);
        }
    }

    /**
     * Update wallet account
     */
    public function update(UpdateRequest $request, WalletAccount $walletAccount)
    {
        try {
            $user = auth()->user();

            if ($user->role->value !== Role::MANAGER->value && $user->id !== $walletAccount->user_id) {
                return $this->sendError("You don't have permission to access this page", [], 403);
            }
            $validated = $request->validated();

            DB::transaction(function () use ($walletAccount, $validated, $user) {

                if ($validated['is_active']) {
                    WalletAccount::where('user_id', $user->id)
                        ->where('type', $walletAccount->type)
                        ->where('id', '!=', $walletAccount->id)
                        ->update(['is_active' => false]);
                }

                $walletAccount->update($validated);
            });

            return $this->sendResponse([], 'Wallet account updated successfully');
        }catch (\Exception $e){
            Log::error("Failed to update account",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to update account",[],500);
        }
    }

    /**
     * Toggle active / inactive
     */
    public function toggleStatus($id)
    {
        try {
            $user = auth()->user();

            $walletAccount = WalletAccount::find($id);
            if (!$walletAccount) {
                return $this->sendError("Wallet account not found", [], 404);
            }

            // permission check
            if (
                $user->role->value !== Role::MANAGER->value &&
                $user->id !== $walletAccount->user_id
            ) {
                return $this->sendError("You don't have permission", [], 403);
            }
            
            DB::transaction(function () use ($walletAccount) {

                // if activating → deactivate ALL others of this user
                if ($walletAccount->is_active === false) {
                    WalletAccount::where('user_id', $walletAccount->user_id)
                        ->update(['is_active' => false]);
                }

                // toggle current one
                $walletAccount->update([
                    'is_active' => !$walletAccount->is_active,
                ]);
            });

            // refresh model to get updated value
            $walletAccount->refresh();

            return $this->sendResponse([
                'is_active' => $walletAccount->is_active
            ], 'Wallet account status updated');

        } catch (\Exception $e) {
            Log::error("Failed to update account", [
                'userId'  => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            return $this->sendError("Failed to update account", [], 500);
        }
    }


    /**
     * Delete wallet account
     */
    public function destroy( $id)
    {
        try {
            $user = auth()->user();

            $walletAccount  = WalletAccount::find($id);
            if(!$walletAccount){
                return $this->sendError("Wallet account not found",[],404);
            }

            if ($user->role->value !== Role::MANAGER->value && $user->id !== $walletAccount->user_id) {
                return $this->sendError("You don't have permission to access this page", [], 403);
            }
            $walletAccount->delete();

            return $this->sendResponse([], 'Wallet account deleted successfully');
        }catch (\Exception $e){
            Log::error("Failed to delete account",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            return $this->sendError("Failed to delete account",[],500);
        }
    }
}
