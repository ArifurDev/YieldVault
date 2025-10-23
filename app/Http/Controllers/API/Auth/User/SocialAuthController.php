<?php

namespace App\Http\Controllers\API\Auth\User;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\SocialAuthRequest;
use App\Http\Resources\Api\V1\Auth\SocialAuthResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SocialAuthController extends Controller
{
    use ApiResponse;
    public function SocialLogin(SocialAuthRequest $request)
    {
        $validated = $request->validated();

        try {
            $provider      = $validated['provider'];
            $socialiteUser = Socialite::driver($provider)->stateless()->userFromToken($validated['token']);

            if ($socialiteUser)
            {
                // check provider and provider id exist or not
                $user = User::where('email',$socialiteUser->getEmail())->where('provider', $provider)
                    ->where('provider_id', $socialiteUser->getId())
                    ->first();

                $isNewUser = false;

                if (!$user)
                {
                    $password = Str::random(16);
                    // store data in database
                    $user = User::create([
                        'first_name'  => $socialiteUser->getName() ? explode(' ', $socialiteUser->getName())[0] : "Apple",
                        'last_name'   => $socialiteUser->getName() ? (count(explode(' ', $socialiteUser->getName())) > 1 ? explode(' ', $socialiteUser->getName())[1] : null) : "User",
                        'email'       => $socialiteUser->getEmail(),
                        'provider'    => $provider,
                        'provider_id' => $socialiteUser->getId(),
                        'password'    => Hash::make($password),
                        'role'        => Role::USER->value,
                    ]);
                    $isNewUser = true;
                }

                $token = auth('api')->login($user);
            }

            return $this->sendResponse(new SocialAuthResource($user),$isNewUser ? "User Register Successfully" : "User Login Successfully",200,$token);
        }catch (\Exception $exception){
            return $this->sendError("Something Went Wrong:". $exception->getMessage(),[],500);
        }
    }
}
