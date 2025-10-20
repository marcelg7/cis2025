<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}