<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\ModuleRoute;
use App\Events\ActivityLogged;

class ActivityLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        /*
        |--------------------------------------------------------------------------
        | EXCEPTION
        |--------------------------------------------------------------------------
        |
        | Dashboard IT dan endpoint Pusher tidak dicatat sebagai
        | aktivitas user.
        |
        */

        if ($request->routeIs([
            'it.index',
            'it.data',
            'pusher.auth',
        ])) {
            return $response;
        }


        /*
        |--------------------------------------------------------------------------
        | HANYA USER LOGIN
        |--------------------------------------------------------------------------
        */

        if (!auth()->check()) {
            return $response;
        }


        /*
        |--------------------------------------------------------------------------
        | ROUTE
        |--------------------------------------------------------------------------
        */

        $route = $request->route();

        $routeName = $route?->getName();


        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        $url = '/' . ltrim(
            $request->path(),
            '/'
        );


        /*
        |--------------------------------------------------------------------------
        | MODULE ROUTE
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
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        $activity = ActivityLog::create([
            'user_id' => auth()->id(),

            'module_id' =>
                $moduleRoute?->module_id,

            'route_name' =>
                $routeName,

            'url' =>
                $url,

            'method' =>
                $request->method(),

            'ip_address' =>
                $request->ip(),

            'user_agent' =>
                $request->userAgent(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | BROADCAST REALTIME
        |--------------------------------------------------------------------------
        */

        event(
            new ActivityLogged($activity)
        );


        return $response;
    }
}