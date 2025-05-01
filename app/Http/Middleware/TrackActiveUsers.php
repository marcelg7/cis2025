<?php

namespace App\Http\Middleware;

use App\Models\ActiveUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;


class TrackActiveUsers
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
	public function handle(Request $request, Closure $next)
	{
		if (Auth::check()) {
			try {
				// Consider a user active if they've been active in the last 5 minutes
				ActiveUser::updateOrCreate(
					['user_id' => Auth::id()],
					['last_activity_at' => now()]
				);
				
				// Clean up old records
				ActiveUser::where('last_activity_at', '<', now()->subMinutes(5))->delete();
			} catch (\Exception $e) {
				// Log the error
				\Illuminate\Support\Facades\Log::error('Active user tracking error: ' . $e->getMessage());
			}
		}
		
		return $next($request);
	}
}
