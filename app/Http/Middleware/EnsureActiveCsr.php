<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCsr
{
    /**
     * Handle an incoming request.
     *
     * If user is on a shared device, ensure they have selected an active CSR
     * before allowing them to create/edit contracts.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not on a shared device, allow request through
        if (!$user || !$user->is_shared_device) {
            return $next($request);
        }

        // User is on shared device - check if CSR is selected
        if (!session()->has('active_csr_id')) {
            // No CSR selected - redirect to selector (but not if they're already there)
            if (!$request->is('csr-selector*')) {
                return redirect()->route('csr-selector.index')
                    ->with('warning', 'Please select which CSR is using this device.');
            }
        }

        return $next($request);
    }
}
