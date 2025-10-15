<?php

namespace App\Http\Controllers;

use App\Models\MobileInternetPlan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MobileInternetPlanController extends Controller
{
    /**
     * Display a listing of mobile internet plans.
     */
    public function index(): View
    {
        $mobileInternetPlans = MobileInternetPlan::current()
            ->active()
            ->orderBy('monthly_rate')
            ->get();

        return view('cellular-pricing.mobile-internet.index', compact('mobileInternetPlans'));
    }

    /**
     * Show the form for creating a new mobile internet plan.
     */
    public function create(): View
    {
        return view('cellular-pricing.mobile-internet.create');
    }

    /**
     * Store a newly created mobile internet plan in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'soc_code' => 'required|string|max:50|unique:mobile_internet_plans,soc_code',
            'plan_name' => 'required|string|max:255',
            'monthly_rate' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'promo_group' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:65535',
            'effective_date' => 'required|date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
            'is_test' => 'boolean',
        ]);

        // Handle checkboxes
        $validated['is_current'] = $request->has('is_current') ? 1 : 1; // Default to current
        $validated['is_active'] = $request->has('is_active') ? 1 : 1; // Default to active
        $validated['is_test'] = $request->has('is_test');

        MobileInternetPlan::create($validated);

        return redirect()
            ->route('cellular-pricing.mobile-internet')
            ->with('success', 'Mobile internet plan created successfully.');
    }

    /**
     * Display the specified mobile internet plan.
     */
    public function show(MobileInternetPlan $mobileInternetPlan): View
    {
        return view('cellular-pricing.mobile-internet.show', compact('mobileInternetPlan'));
    }

    /**
     * Show the form for editing the specified mobile internet plan.
     */
    public function edit(MobileInternetPlan $mobileInternetPlan): View
    {
        return view('cellular-pricing.mobile-internet.edit', compact('mobileInternetPlan'));
    }

    /**
     * Update the specified mobile internet plan in storage.
     */
    public function update(Request $request, MobileInternetPlan $mobileInternetPlan): RedirectResponse
    {
        $validated = $request->validate([
            'soc_code' => 'required|string|max:50|unique:mobile_internet_plans,soc_code,' . $mobileInternetPlan->id,
            'plan_name' => 'required|string|max:255',
            'monthly_rate' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:100',
            'promo_group' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:65535',
            'effective_date' => 'required|date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
            'is_test' => 'boolean',
        ]);

        // Handle checkboxes
        $validated['is_current'] = $request->has('is_current');
        $validated['is_active'] = $request->has('is_active');
        $validated['is_test'] = $request->has('is_test');

        $mobileInternetPlan->update($validated);

        return redirect()
            ->route('cellular-pricing.mobile-internet')
            ->with('success', 'Mobile internet plan updated successfully.');
    }

    /**
     * Remove the specified mobile internet plan from storage.
     */
    public function destroy(MobileInternetPlan $mobileInternetPlan): RedirectResponse
    {
        $mobileInternetPlan->delete();

        return redirect()
            ->route('cellular-pricing.mobile-internet')
            ->with('success', 'Mobile internet plan deleted successfully.');
    }
}