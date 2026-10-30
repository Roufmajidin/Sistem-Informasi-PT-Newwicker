
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
    // Bisa diubah menjadi false untuk mematikan logging
    private const ENABLE_LOG = true;

    // Matikan realtime agar lebih ringan.
    // Activity Log tetap tersimpan di database.
    private const ENABLE_REALTIME = false;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response)
    {
        if (!self::ENABLE_LOG) {
            return;
        }

        try {
            // Hanya user yang login
            if (!auth()->check()) {
                return;
            }

            // Route yang tidak perlu dicatat
            if ($request->routeIs(
                'it.index',
                'it.data',
                'pusher.auth'
            )) {
                return;
            }

            // Abaikan request polling / prefetch
            if (
                $request->is('broadcasting/*') ||
                $request->is('livewire/*') ||
                $request->is('build/*') ||
                $request->is('storage/*') ||
                $request->is('api/polling/*')
            ) {
                return;
            }

            // Abaikan request AJAX GET untuk mengurangi log.
            // POST, PUT, PATCH, DELETE tetap dicatat.
            if (
                $request->ajax() &&
                $request->isMethod('GET')
            ) {
                return;
            }

            $routeName = $request->route()?->getName();

            if (!$routeName) {
                return;
            }

            $method = $request->method();

            // Cari module ID dengan cache 1 jam
            $cacheKey = 'activity_module_' . md5(
                $routeName . '|' . $method
            );

            $moduleId = Cache::remember(
                $cacheKey,
                3600,
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

            if ($moduleId === '__none__') {
                $moduleId = null;
            }

            // Simpan activity log
            $activity = ActivityLog::create([
                'user_id' => auth()->id(),
                'module_id' => $moduleId,
                'route_name' => $routeName,
                'url' => '/' . ltrim($request->path(), '/'),
                'method' => $method,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Realtime opsional
            if (self::ENABLE_REALTIME) {
                event(new ActivityLogged($activity));
            }

        } catch (\Throwable $e) {
            Log::error('ActivityLogMiddleware error', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
