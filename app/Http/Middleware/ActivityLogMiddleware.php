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
    private const ENABLE_LOG = true;
    private const ENABLE_REALTIME = true;
    private const CACHE_SECONDS = 3600;
    private const BROADCAST_INTERVAL = 10;

    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        if (!self::ENABLE_LOG) return;
        try {
            if ($request->routeIs('it.index', 'it.data', 'pusher.auth')) return;
            if (!auth()->check()) return;
            if ($request->is('broadcasting/*', 'livewire/*', 'build/*', 'storage/*', 'api/polling/*')) return;
            if ($request->ajax() && $request->isMethod('GET')) return;

            $routeName = $request->route()?->getName();
            $method = $request->method();
            $moduleId = null;
            if ($routeName) {
                $cached = Cache::remember('activity_module_' . md5($routeName . '|' . $method), self::CACHE_SECONDS,
                    fn () => ModuleRoute::where('route_name', $routeName)
                        ->where('method', $method)->where('is_active', true)
                        ->value('module_id') ?? '__none__'
                );
                $moduleId = $cached === '__none__' ? null : $cached;
            }

            $activity = ActivityLog::create([
                'user_id' => auth()->id(),
                'module_id' => $moduleId,
                'route_name' => $routeName,
                'url' => '/' . ltrim($request->path(), '/'),
                'method' => $method,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if (self::ENABLE_REALTIME && Cache::add('newwicker_activity_broadcast_throttle', true, self::BROADCAST_INTERVAL)) {
                $activity->loadMissing('user');
                event(new ActivityLogged($activity));
            }
        } catch (\Throwable $e) {
            Log::error('ActivityLogMiddleware error', ['message' => $e->getMessage(), 'route' => $request->route()?->getName()]);
        }
    }
}
