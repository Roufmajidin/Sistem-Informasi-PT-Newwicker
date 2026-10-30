<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\ModuleRoute;
use App\Events\ActivityLogged;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ActivityLogMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    // Activity Log tetap aktif
    private const ENABLE_LOG = false;

    // Realtime Pusher dimatikan sementara
    private const ENABLE_REALTIME = false;

    // Cache module route selama 1 jam
    private const CACHE_SECONDS = 3600;

    /*
    |--------------------------------------------------------------------------
    | HANDLE
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request, Closure $next)
    {
        // Langsung proses halaman tanpa query tambahan
        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | TERMINATE - LOGGING SETELAH RESPONSE
    |--------------------------------------------------------------------------
    */

    public function terminate(Request $request, $response)
    {
        if (!self::ENABLE_LOG) {
            return;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | EXCEPTION ROUTES
            |--------------------------------------------------------------------------
            */

            if ($request->routeIs(
                'it.index',
                'it.data',
                'pusher.auth'
            )) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | HANYA USER LOGIN
            |--------------------------------------------------------------------------
            */

            if (!auth()->check()) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ROUTE & METHOD
            |--------------------------------------------------------------------------
            */

            $routeName = $request->route()?->getName();

            $method = $request->method();

            /*
            |--------------------------------------------------------------------------
            | SKIP REQUEST TIDAK PERLU
            |--------------------------------------------------------------------------
            */

            if (
                $request->is('broadcasting/*') ||
                $request->is('livewire/*') ||
                $request->is('build/*') ||
                $request->is('storage/*') ||
                $request->is('api/polling/*')
            ) {
                return;
            }

            // AJAX GET tidak dicatat
            // POST, PUT, PATCH, DELETE tetap dicatat
            if (
                $request->ajax() &&
                $request->isMethod('GET')
            ) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | MODULE ROUTE CACHE
            |--------------------------------------------------------------------------
            */

            $moduleId = null;

            if ($routeName) {

                $cacheKey = 'activity_module_' . md5(
                    $routeName . '|' . $method
                );

                $cachedModuleId = Cache::remember(
                    $cacheKey,
                    self::CACHE_SECONDS,
                    function () use ($routeName, $method) {

                        return ModuleRoute::where(
                            'route_name',
                            $routeName
                        )
                        ->where('method', $method)
                        ->where('is_active', true)
                        ->value('module_id') ?? '__none__';
                    }
                );

                if ($cachedModuleId !== '__none__') {
                    $moduleId = $cachedModuleId;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN ACTIVITY LOG
            |--------------------------------------------------------------------------
            */

            $activity = ActivityLog::create([
                'user_id' => auth()->id(),
                'module_id' => $moduleId,
                'route_name' => $routeName,
                'url' => '/' . ltrim(
                    $request->path(),
                    '/'
                ),
                'method' => $method,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | REALTIME PUSHER (OPTIONAL)
            |--------------------------------------------------------------------------
            */

            if (self::ENABLE_REALTIME) {
                event(new ActivityLogged($activity));
            }

        } catch (\Throwable $e) {

            // Error logging tidak mengganggu halaman utama
            Log::error('ActivityLogMiddleware error', [
                'message' => $e->getMessage(),
                'route' => $request->route()?->getName(),
            ]);
        }
    }
}
