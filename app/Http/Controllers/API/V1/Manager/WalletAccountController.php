<?php

namespace App\Http\Controllers\API\V1\Manager;

use App\Enum\Role;
use App\Helpers\translateHelper;
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
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // get current auth
            $user = auth()->user();
            if ($user->role->value !== Role::MANAGER->value)
            {
                $message = translateHelper::translateCached(
                    'wallet_account_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($message, [], 403);
            }

            $accounts = WalletAccount::where('user_id', $user->id)
                ->latest()
                ->get();

            $successMessage = translateHelper::translateCached(
                'wallet_accounts_list_success',
                'Wallet accounts retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                getAccountResource::collection($accounts),
                $successMessage
            );
        }catch (\Exception $e){
            Log::error("Failed to get accounts list",
            [
                'user_id' => auth()->user()->id,
                'error' => $e->getMessage()
            ]);
            $errorMessage = translateHelper::translateCached(
                'wallet_accounts_list_failed',
                'Failed to retrieve wallet accounts list.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Store wallet account
     */
    public function store(StoreRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            //get current auth
            $user = auth()->user();
            // get current auth
            $user = auth()->user();

            if ($user->role->value !== Role::MANAGER->value) {
                $message = translateHelper::translateCached(
                    'wallet_account_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($message, [], 403);
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


            $successMessage = translateHelper::translateCached(
                'wallet_account_create_success',
                'Wallet account added successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);
        }catch (\Exception $e){
            Log::error("Failed to store account",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $errorMessage = translateHelper::translateCached(
                'wallet_account_create_failed',
                'Failed to store wallet account.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Update wallet account
     */
    public function update(UpdateRequest $request, WalletAccount $walletAccount)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $user = auth()->user();

            if ($user->role->value !== Role::MANAGER->value && $user->id !== $walletAccount->user_id) {
                $message = translateHelper::translateCached(
                    'wallet_account_update_permission_denied',
                    "You don't have permission to access this page.",
                    $lang
                );

                return $this->sendError($message, [], 403);
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

            $successMessage = translateHelper::translateCached(
                'wallet_account_update_success',
                'Wallet account updated successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);
        }catch (\Exception $e){
            Log::error("Failed to update account",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $errorMessage = translateHelper::translateCached(
                'wallet_account_update_failed',
                'Failed to update wallet account.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }

    /**
     * Toggle active / inactive
     */
    public function toggleStatus(Request $request,$id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $user = auth()->user();

            $walletAccount = WalletAccount::find($id);
            if (!$walletAccount) {
                $message = translateHelper::translateCached(
                    'wallet_account_not_found',
                    'Wallet account not found.',
                    $lang
                );

                return $this->sendError($message, [], 404);
            }

            // permission check
            if (
                $user->role->value !== Role::MANAGER->value &&
                $user->id !== $walletAccount->user_id
            ) {
                $message = translateHelper::translateCached(
                    'wallet_account_permission_denied',
                    "You don't have permission to update this wallet account.",
                    $lang
                );

                return $this->sendError($message, [], 403);
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

            $successMessage = translateHelper::translateCached(
                'wallet_account_status_updated',
                'Wallet account status updated successfully.',
                $lang
            );

            return $this->sendResponse([
                'is_active' => (bool) $walletAccount->is_active
            ], $successMessage);

        } catch (\Exception $e) {
            Log::error("Failed to update account", [
                'userId'  => auth()->id(),
                'message' => $e->getMessage(),
            ]);

            $errorMessage = translateHelper::translateCached(
                'wallet_account_status_update_failed',
                'Failed to update wallet account status.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }


    /**
     * Delete wallet account
     */
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            $user = auth()->user();

            $walletAccount  = WalletAccount::find($id);
            if(!$walletAccount){
                $message = translateHelper::translateCached(
                    'wallet_account_not_found',
                    'Wallet account not found.',
                    $lang
                );

                return $this->sendError($message, [], 404);
            }

            if ($user->role->value !== Role::MANAGER->value && $user->id !== $walletAccount->user_id) {
                $message = translateHelper::translateCached(
                    'wallet_account_permission_denied',
                    "You don't have permission to delete this wallet account.",
                    $lang
                );

                return $this->sendError($message, [], 403);
            }
            $walletAccount->delete();

            $successMessage = translateHelper::translateCached(
                'wallet_account_deleted_success',
                'Wallet account deleted successfully.',
                $lang
            );

            return $this->sendResponse([], $successMessage);
        }catch (\Exception $e){
            Log::error("Failed to delete account",[
                'userId' => $user->id,
                'message' => $e->getMessage(),
            ]);
            $errorMessage = translateHelper::translateCached(
                'wallet_account_delete_failed',
                'Failed to delete wallet account.',
                $lang
            );

            return $this->sendError($errorMessage, [], 500);
        }
    }
}
