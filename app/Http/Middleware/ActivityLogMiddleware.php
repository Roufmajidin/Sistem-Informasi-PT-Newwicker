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
    public function handle(Request $request, Closure $next)
    {
        // Tidak menjalankan logging sebelum response.
        return $next($request);
    }

    public function terminate(Request $request, $response)
    {
        try {
            // Route yang tidak perlu dicatat
            if ($request->routeIs(
                'it.index',
                'it.data',
                'pusher.auth'
            )) {
                return;
            }

            // Hanya user login
            if (!auth()->check()) {
                return;
            }

            $routeName = $request->route()?->getName();
            $method = $request->method();

            // Cache lookup module selama 10 menit.
            // Gunakan sentinel agar module_id null tetap bisa dicache.
            $cacheKey = 'activity_module_' . md5(
                ($routeName ?? '') . '|' . $method
            );

            $moduleId = Cache::remember(
                $cacheKey,
                now()->addMinutes(10),
                function () use ($routeName, $method) {
                    return ModuleRoute::where('route_name', $routeName)
                        ->where('method', $method)
                        ->where('is_active', true)
                        ->value('module_id') ?? '__none__';
                }
            );

            if ($moduleId === '__none__') {
                $moduleId = null;
            }

            // Simpan log ke database
            $activity = ActivityLog::create([
                'user_id' => auth()->id(),
                'module_id' => $moduleId,
                'route_name' => $routeName,
                'url' => '/' . ltrim($request->path(), '/'),
                'method' => $method,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Event realtime tetap dipanggil
            event(new ActivityLogged($activity));

        } catch (\Throwable $e) {
            // Logging gagal tidak membuat halaman error.
            Log::error('ActivityLogMiddleware error', [
                'message' => $e->getMessage(),
                'route' => $request->route()?->getName(),
            ]);
        }
    }
}
