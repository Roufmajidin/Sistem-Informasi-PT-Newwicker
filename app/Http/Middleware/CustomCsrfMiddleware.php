<?php
namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class CustomCsrfMiddleware extends Middleware
{
    protected $except = [
        '/login',
        '/logout',
        'login', // tanpa slash awal
        'logout',
        'api/login',
        'api/logout',
    ];
}
