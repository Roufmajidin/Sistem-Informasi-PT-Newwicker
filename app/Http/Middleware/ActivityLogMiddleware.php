<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\ModuleRoute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ActivityLogMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | KONFIGURASI
    |--------------------------------------------------------------------------
    */

    // FALSE = Activity Log nonaktif
    // TRUE  = Activity Log aktif kembali
    private const ENABLE_LOG = false;

    // Cache module route selama 1 jam
    private const CACHE_SECONDS = 3600;

    /*
    |--------------------------------------------------------------------------
    | HANDLE
    |--------------------------------------------------------------------------
    */

    public function handle(Request $request, Closure $next)
    {
        // Tidak ada query database atau broadcasting.
        return $next($request);
    }

    /*
    |--------------------------------------------------------------------------
    | TERMINATE - LOGGING SETELAH RESPONSE
    |--------------------------------------------------------------------------
    */

    public function terminate(Request $request, $response): void
    {
        // Activity Log dimatikan.
        // Tidak ada query atau INSERT ke database.
        if (!self::ENABLE_LOG) {
            return;
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | EXCEPTION ROUTES
            |--------------------------------------------------------------------------
            */

            if (
                $request->routeIs(
                    'it.index',
                    'it.data',
                    'pusher.auth'
                )
            ) {
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

            // AJAX GET tidak dicatat.
            // POST, PUT, PATCH, DELETE tetap dicatat.
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
            | SIMPAN ACTIVITY LOG KE DATABASE
            |--------------------------------------------------------------------------
            */

            ActivityLog::create([
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
            | TIDAK ADA PUSHER / BROADCASTING
            |--------------------------------------------------------------------------
            |
            | Aktivitas hanya disimpan ke tabel activity_logs.
            | Dashboard IT membaca data langsung dari database.
            |
            */

        } catch (\Throwable $e) {

            // Kegagalan logging tidak mengubah respons utama.
            Log::error('ActivityLogMiddleware error', [
                'message' => $e->getMessage(),
                'route' => $request->route()?->getName(),
            ]);
        }
    }
}
