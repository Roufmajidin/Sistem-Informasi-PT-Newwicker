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
    // Semua aktivitas tetap dicatat
    private const ENABLE_LOG = true;

    // Realtime melalui queue Pusher
    private const ENABLE_REALTIME = true;

    // Cache module route 1 jam
    private const CACHE_SECONDS = 3600;

    // Maksimal satu event setiap 10 detik
    private const BROADCAST_INTERVAL = 10;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (!self::ENABLE_LOG) {
            return;
        }

        try {
            // Route yang tidak perlu dicatat
            if ($request->routeIs(
                'it.index',
                'it.data',
                'pusher.auth'
            )) {
                return;
            }

            if (!auth()->check()) {
                return;
            }

            if (
                $request->is('broadcasting/*') ||
                $request->is('livewire/*') ||
                $request->is('build/*') ||
                $request->is('storage/*') ||
                $request->is('api/polling/*')
            ) {
                return;
            }

            // Lewati AJAX GET untuk mengurangi log
            if (
                $request->ajax() &&
                $request->isMethod('GET')
            ) {
                return;
            }

            $routeName = $request->route()?->getName();
            $method = $request->method();

            // Cari module_id dengan cache
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

            // Simpan semua aktivitas menggunakan model lama
            $activity = ActivityLog::create([
                'user_id' => auth()->id(),
                'module_id' => $moduleId,
                'route_name' => $routeName,
                'url' => '/' . ltrim($request->path(), '/'),
                'method' => $method,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Realtime dibatasi agar hemat Pusher
            if (self::ENABLE_REALTIME) {

                // Gunakan cache bersama, misalnya database
                // atau Redis, bukan cache lokal per worker.
                $shouldBroadcast = Cache::add(
                    'newwicker_activity_broadcast_throttle',
                    true,
                    self::BROADCAST_INTERVAL
                );

                if ($shouldBroadcast) {
                    $activity->loadMissing('user');

                    // Event akan masuk database queue
                    event(new ActivityLogged($activity));
                }
            }

        } catch (\Throwable $e) {
            Log::error('ActivityLogMiddleware error', [
                'message' => $e->getMessage(),
                'route' => $request->route()?->getName(),
            ]);
        }
    }
}
