<?php

namespace App\Http\Controllers;

use App\Models\RatePlan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RatePlanController extends Controller
{
    /**
     * Display a listing of rate plans.
     */
    public function index(): View
    {
        $ratePlans = RatePlan::current()
            ->active()
            ->orderBy('plan_type')
            ->orderBy('tier')
            ->orderBy('base_price')
            ->get();

        return view('cellular-pricing.rate-plans.index', compact('ratePlans'));
    }

    /**
     * Show the form for creating a new rate plan.
     */
    public function create(): View
    {
        return view('cellular-pricing.rate-plans.create');
    }

    /**
     * Store a newly created rate plan in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'soc_code' => 'required|string|max:50|unique:rate_plans,soc_code',
            'plan_name' => 'required|string|max:255',
            'plan_type' => 'required|in:byod,smartpay',
            'tier' => 'nullable|in:Lite,Select,Max,Ultra',
            'base_price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'promo_description' => 'nullable|string|max:255',
            'data_amount' => 'nullable|string',
            'is_international' => 'boolean',
            'is_us_mexico' => 'boolean',
            'features' => 'nullable|string|max:65535',
            'effective_date' => 'required|date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
            'is_test' => 'boolean',
        ]);

        // Handle checkboxes
        $validated['is_international'] = $request->has('is_international');
        $validated['is_us_mexico'] = $request->has('is_us_mexico');
        $validated['is_current'] = $request->has('is_current') ? 1 : 1; // Default to current
        $validated['is_active'] = $request->has('is_active') ? 1 : 1; // Default to active
        $validated['is_test'] = $request->has('is_test');

        RatePlan::create($validated);

        return redirect()
            ->route('cellular-pricing.rate-plans')
            ->with('success', 'Rate plan created successfully.');
    }

    /**
     * Display the specified rate plan.
     */
    public function show(RatePlan $ratePlan): View
    {
        return view('cellular-pricing.rate-plans.show', compact('ratePlan'));
    }
	public function edit(RatePlan $ratePlan): View
	{
		// Ensure the effective_date is properly formatted for the input field
		if ($ratePlan->effective_date instanceof \Carbon\Carbon) {
			$ratePlan->effective_date = $ratePlan->effective_date->format('Y-m-d');
		}
		
		return view('cellular-pricing.rate-plans.edit', compact('ratePlan'));
	}

    /**
     * Update the specified rate plan in storage.
     */
	public function update(Request $request, RatePlan $ratePlan): RedirectResponse
	{
		$validated = $request->validate([
			'soc_code' => 'required|string|max:50',
			'plan_name' => 'required|string|max:255',
			'plan_type' => 'required|in:byod,smartpay',
			'tier' => 'nullable|string|max:50',
			'base_price' => 'required|numeric|min:0',
			'promo_price' => 'nullable|numeric|min:0',
			'promo_description' => 'nullable|string|max:500',
			'data_amount' => 'nullable|string|max:100',
			'effective_date' => 'required|date',
			'is_international' => 'boolean',
			'is_us_mexico' => 'boolean',
			'is_active' => 'boolean',
			'features' => 'nullable|string', // THIS IS CRITICAL
		]);

		// Handle checkboxes (they won't be in $validated if unchecked)
		$validated['is_international'] = $request->has('is_international');
		$validated['is_us_mexico'] = $request->has('is_us_mexico');
		$validated['is_active'] = $request->has('is_active');

		$ratePlan->update($validated);

		return redirect()
			->route('cellular-pricing.rate-plans')
			->with('success', 'Rate plan updated successfully.');
	}

    /**
     * Remove the specified rate plan from storage.
     */
    public function destroy(RatePlan $ratePlan): RedirectResponse
    {
        $ratePlan->delete();

        return redirect()
            ->route('cellular-pricing.rate-plans')
            ->with('success', 'Rate plan deleted successfully.');
    }
}