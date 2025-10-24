<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $canViewAll = auth()->user()->can('view_all_analytics');
        $includeTaxes = $request->boolean('include_taxes', false);

        // Base query - show all contracts (no user filtering available)
        $baseQuery = Contract::query();

        // Time ranges
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Contracts created today/this week/this month
        $contractsToday = (clone $baseQuery)->whereDate('created_at', $today)->count();
        $contractsThisWeek = (clone $baseQuery)->where('created_at', '>=', $weekStart)->count();
        $contractsThisMonth = (clone $baseQuery)->where('created_at', '>=', $monthStart)->count();

        // Contracts by status
        $contractsByStatus = (clone $baseQuery)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Most popular devices this month (top 5)
        $popularDevices = (clone $baseQuery)
            ->where('created_at', '>=', $monthStart)
            ->whereNotNull('bell_device_id')
            ->select('bell_device_id', DB::raw('count(*) as count'))
            ->with('bellDevice:id,name')
            ->groupBy('bell_device_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->bellDevice->name ?? 'Unknown Device',
                    'count' => $item->count
                ];
            });

        // Most popular rate plans this month (top 5)
        $popularPlans = (clone $baseQuery)
            ->where('created_at', '>=', $monthStart)
            ->whereNotNull('rate_plan_id')
            ->select('rate_plan_id', DB::raw('count(*) as count'))
            ->with('ratePlan:id,plan_name')
            ->groupBy('rate_plan_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->ratePlan->plan_name ?? 'Unknown Plan',
                    'count' => $item->count
                ];
            });

        // Revenue projections from finalized contracts only
        $finalizedContracts = (clone $baseQuery)
            ->where('status', 'finalized')
            ->with(['addOns', 'oneTimeFees'])
            ->get();

        $totalRevenue = 0;
        foreach ($finalizedContracts as $contract) {
            // Device revenue (retail - credit - upfront payments)
            $deviceRevenue = ($contract->bell_retail_price ?? 0)
                           - ($contract->agreement_credit_amount ?? 0)
                           - ($contract->required_upfront_payment ?? 0)
                           - ($contract->optional_down_payment ?? 0);

            // Rate plan revenue (monthly × 24)
            $ratePlanRevenue = ($contract->rate_plan_price ?? $contract->bell_plan_cost ?? 0) * 24;

            // Add-ons revenue (sum of all add-ons × 24)
            $addOnsRevenue = $contract->addOns->sum('price') * 24;

            // One-time fees
            $oneTimeFees = $contract->oneTimeFees->sum('amount');

            $contractTotal = $deviceRevenue + $ratePlanRevenue + $addOnsRevenue + $oneTimeFees;
            $totalRevenue += $contractTotal;
        }

        // Apply taxes if requested
        $revenueWithTaxes = $totalRevenue * 1.13;

        // CSR performance (contracts per user based on updated_by)
        $csrPerformance = Contract::query()
            ->select('updated_by', DB::raw('count(*) as count'))
            ->whereNotNull('updated_by')
            ->with('updatedBy:id,name')
            ->groupBy('updated_by')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->updatedBy->name ?? 'Unknown User',
                    'count' => $item->count
                ];
            });

        // Contracts trend (last 30 days, day by day)
        $contractsTrend = (clone $baseQuery)
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        return view('analytics.index', compact(
            'contractsToday',
            'contractsThisWeek',
            'contractsThisMonth',
            'contractsByStatus',
            'popularDevices',
            'popularPlans',
            'totalRevenue',
            'revenueWithTaxes',
            'includeTaxes',
            'csrPerformance',
            'contractsTrend',
            'canViewAll'
        ));
    }
}
