<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Helpers\helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Profile\getResource;
use App\Http\Resources\Api\V1\Profile\UpdateResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
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
            $auth->bio        = $validated['bio'];

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
}
