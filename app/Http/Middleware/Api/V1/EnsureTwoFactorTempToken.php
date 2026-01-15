<?php

namespace App\Http\Middleware\Api\V1;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorTempToken
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // JWT payload read
            $payload = JWTAuth::parseToken()->getPayload();

            /**
             * TEMP token must have:
             * two_factor = true
             * type = 2fa
             */
            if (
                $payload->get('two_factor') !== true ||
                $payload->get('type') !== '2fa'
            ) {
                return $this->sendError('This action requires two-factor verification.',[],403);
            }

        } catch (\Exception $e) {
            return $this->sendError('Invalid or expired verification session.',[],401);
        }

        return $next($request);
    }
}
