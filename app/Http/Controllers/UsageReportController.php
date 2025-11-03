<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsageReportController extends Controller
{
    public function index(Request $request)
    {
        // Date range filter (default to last 30 days)
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Convert to Carbon instances
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Get user filter
        $userId = $request->input('user_id');

        // Build base query
        $sessionsQuery = UserSession::query()
            ->with('user')
            ->betweenDates($start, $end);

        if ($userId) {
            $sessionsQuery->where('user_id', $userId);
        }

        // Get usage statistics per user
        $userStats = User::query()
            ->where('is_shared_device', false) // Exclude shared device accounts
            ->withCount(['sessions as total_sessions' => function ($query) use ($start, $end) {
                $query->betweenDates($start, $end);
            }])
            ->withSum(['sessions as total_duration' => function ($query) use ($start, $end) {
                $query->betweenDates($start, $end);
            }], 'duration_seconds')
            ->with(['sessions' => function ($query) use ($start, $end) {
                $query->betweenDates($start, $end)
                    ->latest('login_at')
                    ->limit(1);
            }])
            ->having('total_sessions', '>', 0)
            ->orderByDesc('total_duration')
            ->get()
            ->map(function ($user) {
                $avgDuration = $user->total_sessions > 0
                    ? $user->total_duration / $user->total_sessions
                    : 0;

                return [
                    'user' => $user,
                    'total_sessions' => $user->total_sessions,
                    'total_duration_seconds' => $user->total_duration ?? 0,
                    'total_duration_formatted' => $this->formatDuration($user->total_duration ?? 0),
                    'avg_duration_seconds' => $avgDuration,
                    'avg_duration_formatted' => $this->formatDuration($avgDuration),
                    'last_login' => $user->sessions->first()?->login_at,
                ];
            });

        // Get recent sessions
        $recentSessions = UserSession::query()
            ->with('user')
            ->betweenDates($start, $end)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->latest('login_at')
            ->paginate(20);

        // Get currently active sessions
        $activeSessions = UserSession::query()
            ->with('user')
            ->active()
            ->latest('login_at')
            ->get();

        // Daily activity chart data
        $dailyActivity = UserSession::query()
            ->betweenDates($start, $end)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->select(
                DB::raw('DATE(login_at) as date'),
                DB::raw('COUNT(*) as login_count'),
                DB::raw('SUM(duration_seconds) as total_duration')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // All users for filter dropdown
        $users = User::where('is_shared_device', false)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('reports.usage', compact(
            'userStats',
            'recentSessions',
            'activeSessions',
            'dailyActivity',
            'users',
            'startDate',
            'endDate',
            'userId'
        ));
    }

    /**
     * Format duration in seconds to human-readable format
     */
    private function formatDuration($seconds): string
    {
        if (!$seconds) {
            return '0 min';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return sprintf('%d h %d min', $hours, $minutes);
        } else {
            return sprintf('%d min', $minutes);
        }
    }
}
