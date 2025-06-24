<?php
use App\Http\Middleware\CustomCsrfMiddleware;
use App\Http\Middleware\DisableCsrfForLogin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
            api: __DIR__.'/../routes/api.php', // ✅ tambahkan ini

        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
  ->withMiddleware(function (\Illuminate\Foundation\Configuration\Middleware $middleware) {
    $middleware->alias([
        'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);


    $middleware->appendToGroup('api', [
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ]);;
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
