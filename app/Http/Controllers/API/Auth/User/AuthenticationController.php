<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\OtpVerifyRequest;
use App\Http\Requests\Api\V1\Auth\SignupRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use App\Mail\Api\V1\SendOtpMail;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use ApiResponse;
    /**
     * user registration
     */
    public function signup(SignupRequest $request)
    {
        $validated = $request->validated();
        try {
            // store data in database
            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role'       => Role::USER->value,
            ]);

            $token = auth('api')->login($user);

            // success response
            return $this->sendResponse(new UserResource($user),'User registration successful.',200,$token);
        }catch (\Exception $exception){
            Log::error("Failed to create account:".$exception->getMessage());
            return $this->sendError("Failed to create account:".$exception->getMessage(),[],500);
        }
    }

    /**
     * signing
     */
    public function login(LoginRequest $request)
    {
        $validatedData = $request->validated();
        try {

            // Attempt to log the user in
            if (!$token = JWTAuth::attempt($validatedData)) {
                return $this->sendError('Invalid credentials. Please check your email and password.', [], 401);
            }

            $user = Auth::user();

            return $this->sendResponse(new UserResource($user), 'User logged in successfully.', 200, $token);
        }catch (\Exception $exception){
            Log::error("Failed to login:".$exception->getMessage());
            return $this->sendError("Failed to login:".$exception->getMessage(),[],500);
        }
    }

}
