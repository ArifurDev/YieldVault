<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\Api\V1\HandleDailyStreak;
use App\Http\Middleware\Api\V1\IsManagerMiddleware;
use App\Http\Middleware\JWTMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        then: function () {
            Route::middleware(['web', 'admin','auth'])->prefix('admin')
            ->group(base_path('routes/backend.php'));
    }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
        'admin'        => AdminMiddleware::class,
        'jwt.verify'   => JWTMiddleware::class,
        'daily.streak' => HandleDailyStreak::class,
        'is_manager'   => IsManagerMiddleware::class,
        'role'         => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission'   => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Page Not Found.',
                    'code'    => 404,
                ], 404);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Method Not Allowed.',
                    'code'    => 405,
                ], 405);
            }
        });
    })->create();
