<?php
namespace App\Http\Middleware; 

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin {
    public function handle(Request $request, Closure $next): Response {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        return $next($request);
    }
}
