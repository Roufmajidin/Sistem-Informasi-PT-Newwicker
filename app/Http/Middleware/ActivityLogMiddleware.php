<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\ModuleRoute;
// use App\Events\ActivityLogged;

class ActivityLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        /*
        |--------------------------------------------------------------------------
        | EXCEPTION: IT DASHBOARD
        |--------------------------------------------------------------------------
        | Request dari dashboard monitoring tidak dianggap sebagai
        | aktivitas user dan tidak boleh membuat ActivityLog baru.
        |
        | Ini mencegah looping:
        | ActivityLog -> Pusher -> IT Dashboard -> ActivityLog -> ...
        |--------------------------------------------------------------------------
        */
        if (
            $request->routeIs([
                'it.index',
                'it.data',
            ])
        ) {
            return $response;
        }

        /*
        |--------------------------------------------------------------------------
        | Hanya catat user yang sudah login
        |--------------------------------------------------------------------------
        */
        if (auth()->check()) {

            $route = $request->route();

            /*
            |--------------------------------------------------------------------------
            | Ambil route Laravel
            |--------------------------------------------------------------------------
            */
            $routeName = $route?->getName();

            /*
            |--------------------------------------------------------------------------
            | Ambil URL
            |--------------------------------------------------------------------------
            */
            $url = '/' . ltrim(
                $request->path(),
                '/'
            );

            /*
            |--------------------------------------------------------------------------
            | Cari route di module_routes
            |--------------------------------------------------------------------------
            */
            $moduleRoute = ModuleRoute::where(
                'route_name',
                $routeName
            )
                ->where(
                    'method',
                    $request->method()
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();

            /*
            |--------------------------------------------------------------------------
            | Simpan Activity Log
            |--------------------------------------------------------------------------
            */
            $activity = ActivityLog::create([
                'user_id' => auth()->id(),
                'module_id' => $moduleRoute?->module_id,
                'route_name' => $routeName,
                'url' => $url,
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Broadcast realtime - MATI SEMENTARA
            |--------------------------------------------------------------------------
            */
            // event(new ActivityLogged($activity));
        }

        /*
        |--------------------------------------------------------------------------
        | Response WAJIB dikembalikan
        |--------------------------------------------------------------------------
        */
        return $response;
    }
}