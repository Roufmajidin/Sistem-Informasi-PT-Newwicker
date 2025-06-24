<?php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class DisableCsrfForLogin extends Middleware
{protected function inExceptArray($request)
    {
    $except = [
        'login',
        'logout',
    ];

    return collect($except)->contains(function ($except) use ($request) {
        return $request->is($except);
    });
}}
