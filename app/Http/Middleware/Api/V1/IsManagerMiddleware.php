<?php

namespace App\Http\Middleware\Api\V1;

use App\Enum\Role;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsManagerMiddleware
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Not authenticated
        if (!$user)
        {
            return $this->sendError("Unauthenticated", [],403);
        }

        // Not a manager
        if ($user->role !== Role::MANAGER->value) {
            return $this->sendError(
                'Access denied. Manager access required.',
                [],403.
            );
        }

        return $next($request);
    }
}
