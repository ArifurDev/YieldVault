<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Helpers\helpers;
use App\Helpers\translateHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\NotificationSwitchRequest;
use App\Http\Requests\Api\V1\Profile\PasswordUpdateRequest;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Profile\getResource;
use App\Http\Resources\Api\V1\Profile\UpdateResource;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * get auth user profile
     */
    public function index(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // get current auth
            $auth = auth()->user();

            $message = translateHelper::translateCached(
                'user_profile_retrieved',
                'User profile retrieved successfully.',
                $lang
            );

            return $this->sendResponse(
                new getResource($auth),
                $message
            );
        }catch (\Exception $exception){
            $message = translateHelper::translateCached(
                'user_profile_retrieve_failed',
                'Failed to retrieve user profile. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * update auth profile
     */
    public function update(UpdateRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        try {
            // get current auth
            $auth = auth()->user();

            // Normalize input → English
            $firstNameEn = translateHelper::toEnglish(
                $validated['first_name'],
                $lang
            );

            $lastNameEn = translateHelper::toEnglish(
                $validated['last_name'],
                $lang
            );

            // Save ONLY English in main table
            $auth->first_name = $firstNameEn;
            $auth->last_name  = $lastNameEn;
            $auth->email      = $validated['email'];

            $imagePaths = $auth->avatar;
            // Update avatar if provided
            if ($request->hasFile('avatar')) {
                if (!empty($auth->avatar)) {
                    Helpers::deleteFile($auth->avatar,config('app.file_system'));
                }

                $file = $request->file('avatar');
                $mediaFile = time() . Str::random(10) . Str::uuid() . '_' . date('Ymd_His') . '.' . $file->getClientOriginalExtension();
                $imagePaths = Helpers::uploadFile('avatar',$file,$mediaFile,config('app.file_system')); // store url
            }
            $auth->avatar = $imagePaths;
            $auth->save();

            /// If input was NOT English → store translation
            if ($lang !== 'en') {

                $firstNameKey = 'value_first_name_' . md5($auth->id);
                $lastNameKey  = 'value_last_name_' . md5($auth->id);

                // Cleanup old cache (avoid stale data)
                Cache::forget("translation.{$firstNameKey}.{$lang}");
                Cache::forget("translation.{$lastNameKey}.{$lang}");

                // Store translations
                translateHelper::translateCached(
                    $firstNameKey,
                    $validated['first_name'],
                    $lang
                );

                translateHelper::translateCached(
                    $lastNameKey,
                    $validated['last_name'],
                    $lang
                );
            }

            $message = translateHelper::translateCached(
                'profile_update_success',
                'User profile updated successfully.',
                $lang
            );

            return $this->sendResponse(
                new UpdateResource($auth),
                $message
            );

        }catch (\Exception $exception){
            $message = translateHelper::translateCached(
                'profile_update_failed',
                'Failed to update profile. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * logout
     */
    public function logout(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        try {
            // Invalidate the current user's token
            JWTAuth::invalidate(JWTAuth::getToken());

            $message = translateHelper::translateCached(
                'auth_logout_success',
                'Successfully logged out.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (JWTException $e) {
            $message = translateHelper::translateCached(
                'auth_logout_failed',
                'Failed to log out. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * update notification if notification is true that user get push notification
     */
    public function notificationSwitch(NotificationSwitchRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();
        try {
            $user = auth()->user();

            $user->is_notify = $validated['is_notify'];
            $user->save();

            $message = translateHelper::translateCached(
                'notification_update_success',
                'Notification status updated successfully.',
                $lang
            );

            return $this->sendResponse([
                'is_notify' => $user->is_notify
            ], $message);
        }catch (\Exception $e){
            Log::error("Failed to update notification status: " . $e->getMessage());
            $message = translateHelper::translateCached(
                'notification_update_failed',
                'Failed to update notification status. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * update password
     */
    public function updatePassword(PasswordUpdateRequest $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        $validated = $request->validated();

        try {

            $user = auth()->user(); // get current auth

            // verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                $message = translateHelper::translateCached(
                    'password_current_incorrect',
                    'Current password is incorrect.',
                    $lang
                );

                return $this->sendError($message, [], 422);
            }

            $user->password = Hash::make($validated['new_password']);
            $user->save();

            $message = translateHelper::translateCached(
                'password_update_success',
                'Password updated successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        }catch (\Exception $e){
            Log::error("Failed to update password: " . $e->getMessage());
            $message = translateHelper::translateCached(
                'password_update_failed',
                'Failed to update password. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }

    /**
     * refresh token
     */
    public function refreshToken()
    {

    }

    /**
     * Soft delete user account (Anonymize)
     */
    public function deleteAccount(Request $request)
    {
        $lang = $request->header('Accept-Language', 'en');
        DB::beginTransaction();

        try {
            $user = auth()->user();

            // Build unique deleted email
            $deletedEmail = 'deleted_' . $user->id . '_' . time() . '@user.com';

            // Delete avatar if exists
            if (!empty($user->avatar)) {
                Helpers::deleteFile($user->avatar, config('app.file_system'));
            }

            // Update user (anonymize)
            $user->update([
                'first_name' => 'Anonymous',
                'last_name'  => 'User',
                'email'      => $deletedEmail,
                'avatar'     => null,
                'kyc_status' => 'Rejected',
            ]);

            // Delete firebase tokens
            $user->firebaseTokens()->delete();

            // revoke API tokens
            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }
            DB::commit();
            $message = translateHelper::translateCached(
                'account_delete_success',
                'Account deleted successfully.',
                $lang
            );

            return $this->sendResponse([], $message);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Delete account failed', [
                'user_id' => auth()->id(),
                'error'   => $e->getMessage(),
            ]);

            $message = translateHelper::translateCached(
                'account_delete_failed',
                'Failed to delete account. Please try again later.',
                $lang
            );

            return $this->sendError($message, [], 500);
        }
    }
}
