<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get CSR-specific stats
        $stats = [
            // Today's stats
            'drafts_today' => Contract::where('created_by', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->where('status', 'draft')
                ->count(),

            'contracts_today' => Contract::where('created_by', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count(),

            // Pending signatures (contracts in draft or pending state)
            'pending_signatures' => Contract::where('created_by', $user->id)
                ->whereIn('status', ['draft', 'pending'])
                ->count(),

            // Ready to finalize (signed but not finalized)
            'ready_to_finalize' => Contract::where('created_by', $user->id)
                ->where('status', 'signed')
                ->count(),

            // This week's stats
            'contracts_this_week' => Contract::where('created_by', $user->id)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count(),

            // This month's stats
            'contracts_this_month' => Contract::where('created_by', $user->id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
        ];

        // Get recent contracts for this CSR
        $recentContracts = Contract::where('created_by', $user->id)
            ->with('subscriber.mobilityAccount.ivueAccount.customer', 'bellDevice', 'ratePlan')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Get currently active users from sessions (excluding current user)
        $activeUserIds = \DB::table('sessions')
            ->where('user_id', '!=', $user->id)
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(15)->timestamp)
            ->pluck('user_id')
            ->unique();

        $activeUsers = User::whereIn('id', $activeUserIds)
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('stats', 'recentContracts', 'activeUsers'));
    }
}
