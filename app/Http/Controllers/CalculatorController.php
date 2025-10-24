<?php

namespace App\Http\Controllers;

use App\Models\PlanComparison;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Models\BellDevice;
use App\Models\PlanAddOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class CalculatorController extends Controller
{
    /**
     * Display the calculator index page
     */
    public function index()
    {
        $ratePlans = RatePlan::where('is_active', true)
            ->where('is_current', true)
            ->where('is_test', false)
            ->orderBy('plan_name')
            ->get();

        $mobileInternetPlans = MobileInternetPlan::where('is_active', true)
            ->where('is_current', true)
            ->where('is_test', false)
            ->orderBy('plan_name')
            ->get();

        $bellDevices = BellDevice::where('is_active', true)
            ->orderBy('name')
            ->get();

        $addOns = PlanAddOn::where('is_active', true)
            ->where('is_current', true)
            ->where('is_test', false)
            ->orderBy('add_on_name')
            ->get();

        $savedComparisons = PlanComparison::where('created_by', auth()->id())
            ->latest()
            ->take(10)
            ->get();

        return view('calculator.index', compact(
            'ratePlans',
            'mobileInternetPlans',
            'bellDevices',
            'addOns',
            'savedComparisons'
        ));
    }

    /**
     * Calculate and compare plans
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'plans' => 'required|array|min:1|max:4',
            'plans.*' => 'required|exists:rate_plans,id',
            'device_id' => 'nullable|exists:bell_devices,id',
            'financing_type' => 'nullable|in:byod,smartpay,dro',
            'agreement_credit' => 'nullable|numeric|min:0',
            'upfront_payment' => 'nullable|numeric|min:0',
            'down_payment' => 'nullable|numeric|min:0',
            'mobile_internet_id' => 'nullable|exists:mobile_internet_plans,id',
            'add_ons' => 'nullable|array',
            'add_ons.*' => 'exists:plan_add_ons,id',
        ]);

        $calculations = [];

        foreach ($request->plans as $planId) {
            $calculations[] = $this->calculatePlanCost($request, $planId);
        }

        // Find best options
        $bestValue = $this->findBestValue($calculations);
        $lowestMonthly = $this->findLowestMonthly($calculations);
        $lowestTotal = $this->findLowestTotal($calculations);

        return response()->json([
            'calculations' => $calculations,
            'best_value' => $bestValue,
            'lowest_monthly' => $lowestMonthly,
            'lowest_total' => $lowestTotal,
        ]);
    }

    /**
     * Calculate cost for a single plan
     */
    private function calculatePlanCost(Request $request, $planId)
    {
        $plan = RatePlan::findOrFail($planId);

        // Base plan cost
        $planCost = $plan->effective_price ?? 0;

        // Apply Credit if applicable (e.g., Hay Credit)
        $creditAmount = 0;
        $creditDuration = 0;
        if ($plan->credit_amount && $plan->credit_duration) {
            $creditAmount = $plan->credit_amount;
            $creditDuration = $plan->credit_duration;
        }

        // Mobile internet cost
        $internetCost = 0;
        $internetPlan = null;
        if ($request->mobile_internet_id) {
            $internetPlan = MobileInternetPlan::find($request->mobile_internet_id);
            $internetCost = $internetPlan?->monthly_rate ?? 0;
        }

        // Add-ons cost
        $addOnsCost = 0;
        $addOns = [];
        if ($request->add_ons && is_array($request->add_ons)) {
            $addOns = PlanAddOn::whereIn('id', $request->add_ons)->get();
            $addOnsCost = $addOns->sum('monthly_rate');
        }

        // Device costs
        $device = null;
        $deviceRetailPrice = 0;
        $deviceMonthlyCost = 0;
        $totalFinanced = 0;
        $upfrontCosts = 0;

        if ($request->device_id && $request->financing_type !== 'byod') {
            $device = BellDevice::find($request->device_id);

            if ($device && $request->financing_type === 'smartpay') {
                // Get SmartPay pricing
                $pricing = $device->currentPricing()->first();
                if ($pricing) {
                    $deviceRetailPrice = $pricing->retail_price;
                    $agreementCredit = $request->agreement_credit ?? 0;
                    $upfrontPayment = $request->upfront_payment ?? 0;
                    $downPayment = $request->down_payment ?? 0;

                    $totalFinanced = max(0, $deviceRetailPrice - $agreementCredit - $upfrontPayment - $downPayment);
                    $deviceMonthlyCost = $totalFinanced / 24;
                    $upfrontCosts = $upfrontPayment + $downPayment;
                }
            } elseif ($device && $request->financing_type === 'dro') {
                // Get DRO pricing
                $pricing = $device->currentDroPricing()->first();
                if ($pricing) {
                    $deviceRetailPrice = $pricing->retail_price;
                    $upfrontCosts = $pricing->dro_amount;
                    $deviceMonthlyCost = 0; // DRO is paid upfront
                }
            }
        }

        // Calculate monthly costs
        $baseMonthly = $planCost + $internetCost + $addOnsCost + $deviceMonthlyCost;

        // Calculate costs with Credit applied (if any)
        $monthlyCostsArray = [];
        for ($month = 1; $month <= 24; $month++) {
            $monthlyTotal = $baseMonthly;
            if ($month <= $creditDuration) {
                $monthlyTotal -= $creditAmount;
            }
            $monthlyCostsArray[] = $monthlyTotal;
        }

        $total24MonthCost = array_sum($monthlyCostsArray) + $upfrontCosts;
        $avgMonthlyCost = $total24MonthCost / 24;

        // Calculate total credit savings
        $totalCredit = $creditAmount * $creditDuration;

        return [
            'plan_id' => $planId,
            'plan_name' => $plan->plan_name,
            'plan' => $plan,
            'internet_plan' => $internetPlan,
            'device' => $device,
            'add_ons' => $addOns,
            'costs' => [
                'plan' => $planCost,
                'internet' => $internetCost,
                'add_ons' => $addOnsCost,
                'device_monthly' => $deviceMonthlyCost,
                'base_monthly' => $baseMonthly,
                'avg_monthly' => $avgMonthlyCost,
                'upfront' => $upfrontCosts,
                'device_retail' => $deviceRetailPrice,
                'device_financed' => $totalFinanced,
                'total_24_months' => $total24MonthCost,
                'credit_total' => $totalCredit,
                'credit_duration' => $creditDuration,
                'monthly_breakdown' => $monthlyCostsArray,
            ],
            'features' => [
                'data' => $plan->data_amount ?? 'N/A',
                'canada_wide' => $plan->canada_wide_calling ?? false,
                'text' => $plan->unlimited_text ?? false,
                'voice' => $plan->unlimited_voice ?? false,
            ],
        ];
    }

    /**
     * Find the best value option (lowest total cost)
     */
    private function findBestValue($calculations)
    {
        return collect($calculations)->sortBy('costs.total_24_months')->first();
    }

    /**
     * Find the lowest monthly cost option
     */
    private function findLowestMonthly($calculations)
    {
        return collect($calculations)->sortBy('costs.avg_monthly')->first();
    }

    /**
     * Find the lowest total cost option
     */
    private function findLowestTotal($calculations)
    {
        return collect($calculations)->sortBy('costs.total_24_months')->first();
    }

    /**
     * Save a comparison for future reference
     */
    public function save(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'comparison_data' => 'required|array',
            'lowest_monthly_cost' => 'required|numeric',
            'lowest_total_cost' => 'required|numeric',
            'plan_count' => 'required|integer|min:1|max:4',
        ]);

        $comparison = PlanComparison::create([
            'created_by' => auth()->id(),
            'name' => $request->name,
            'notes' => $request->notes,
            'comparison_data' => $request->comparison_data,
            'lowest_monthly_cost' => $request->lowest_monthly_cost,
            'lowest_total_cost' => $request->lowest_total_cost,
            'plan_count' => $request->plan_count,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comparison saved successfully',
            'comparison' => $comparison,
        ]);
    }

    /**
     * Load a saved comparison
     */
    public function load($id)
    {
        $comparison = PlanComparison::where('created_by', auth()->id())
            ->findOrFail($id);

        return response()->json($comparison);
    }

    /**
     * Delete a saved comparison
     */
    public function delete($id)
    {
        $comparison = PlanComparison::where('created_by', auth()->id())
            ->findOrFail($id);

        $comparison->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comparison deleted successfully',
        ]);
    }

    /**
     * Export comparison to PDF
     */
    public function exportPdf(Request $request)
    {
        $calculations = $request->input('calculations');
        $bestValue = $request->input('best_value');

        $pdf = PDF::loadView('calculator.pdf', compact('calculations', 'bestValue'));

        return $pdf->download('plan_comparison_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Email comparison to customer
     */
    public function email(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'calculations' => 'required|array',
            'best_value' => 'required|array',
        ]);

        // TODO: Implement email sending with comparison details
        // This would use Laravel's Mail facade to send the comparison

        return response()->json([
            'success' => true,
            'message' => 'Comparison sent to ' . $request->email,
        ]);
    }
}
