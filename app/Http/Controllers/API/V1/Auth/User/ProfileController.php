<?php

namespace App\Http\Controllers\API\V1\Auth\User;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\NotificationSwitchRequest;
use App\Http\Requests\Api\V1\Profile\PasswordUpdateRequest;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Profile\getResource;
use App\Http\Resources\Api\V1\Profile\UpdateResource;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * get auth user profile
     */
    public function index()
    {
        try {
            // get current auth
            $auth = auth()->user();

            return $this->sendResponse(new getResource($auth), 'User profile retrieved successfully.');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(),[],500);
        }
    }

    /**
     * update auth profile
     */
    public function update(UpdateRequest $request)
    {
        $validated = $request->validated();

        try {
            // get current auth
            $auth = auth()->user();
            $auth->first_name = $validated['first_name'];
            $auth->last_name  = $validated['last_name'];
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

            return $this->sendResponse(new UpdateResource($auth), 'User profile updated successfully.');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(),[],500);
        }
    }

    /**
     * logout
     */
    public function logout()
    {
        try {
            // Invalidate the current user's token
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->sendResponse([], 'Successfully logged out.');
        } catch (JWTException $e) {
            return $this->sendError('Failed to log out, please try again later', [], 500);
        }
    }

    /**
     * update notification if notification is true that user get push notification
     */
    public function notificationSwitch(NotificationSwitchRequest $request)
    {
        $validated = $request->validated();
        try {
            $user = auth()->user();

            $user->is_notify = $validated['is_notify'];
            $user->save();

            return $this->sendResponse([
                'is_notify' => $user->is_notify
            ], 'Notification status updated successfully.');
        }catch (\Exception $e){
            Log::error("Failed to update notification status: " . $e->getMessage());
            return $this->sendError("Failed to update notification status:".$e->getMessage(),[],500);
        }
    }

    /**
     * update password
     */
    public function updatePassword(PasswordUpdateRequest $request)
    {
        $validated = $request->validated();

        try {

            $user = auth()->user(); // get current auth

            // verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return $this->sendError('Current password is incorrect.', [], 422);
            }

            $user->password = Hash::make($validated['current_password']);
            $user->save();
            return $this->sendResponse([], 'Password updated successfully.');
        }catch (\Exception $e){
            Log::error("Failed to update password: " . $e->getMessage());
            return $this->sendError("Failed to update password:".$e->getMessage(),[],500);
        }
    }

    /**
     * refresh token
     */
    public function refreshToken()
    {

    }
}
