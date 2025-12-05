<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ContractTemplateController extends Controller
{
    /**
     * Display a listing of templates (management page)
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's personal templates
        $personalTemplates = ContractTemplate::personal($user->id)
            ->with(['bellDevice', 'ratePlan', 'activityType'])
            ->popular()
            ->get();

        // Get team templates
        $teamTemplates = ContractTemplate::team()
            ->with(['bellDevice', 'ratePlan', 'activityType'])
            ->popular()
            ->get();

        return view('contract-templates.index', compact('personalTemplates', 'teamTemplates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('contract-templates.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_team_template' => 'nullable|boolean',
            'activity_type_id' => 'nullable|exists:activity_types,id',
            'bell_device_id' => 'nullable|exists:bell_devices,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
            'commitment_period_id' => 'nullable|exists:commitment_periods,id',
            'location_id' => 'nullable|exists:locations,id',
            'selected_add_ons' => 'nullable|array',
            'selected_add_ons.*' => 'exists:plan_add_ons,id',
            'selected_one_time_fees' => 'nullable|array',
            'hay_credit_applied' => 'nullable|boolean',
            'is_byod' => 'nullable|boolean',
            'connection_fee_override' => 'nullable|numeric',
        ]);

        // Set user_id
        $validated['user_id'] = Auth::id();

        // Only admins can create team templates
        if ($request->boolean('is_team_template') && !Auth::user()->hasRole('admin')) {
            $validated['is_team_template'] = false;
        }

        $template = ContractTemplate::create($validated);

        return redirect()->route('contract-templates.index')
            ->with('success', 'Template created successfully!');
    }

    /**
     * Display the specified template
     */
    public function show(ContractTemplate $contractTemplate)
    {
        $contractTemplate->load([
            'bellDevice',
            'ratePlan',
            'mobileInternetPlan',
            'activityType',
            'commitmentPeriod',
            'location'
        ]);

        return view('contract-templates.show', compact('contractTemplate'));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit(ContractTemplate $contractTemplate)
    {
        // Users can only edit their own templates, unless they're admin or it's a team template they have permission to edit
        if ($contractTemplate->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        return view('contract-templates.edit', compact('contractTemplate'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, ContractTemplate $contractTemplate)
    {
        // Users can only update their own templates, unless they're admin
        if ($contractTemplate->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_team_template' => 'nullable|boolean',
            'activity_type_id' => 'nullable|exists:activity_types,id',
            'bell_device_id' => 'nullable|exists:bell_devices,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
            'commitment_period_id' => 'nullable|exists:commitment_periods,id',
            'location_id' => 'nullable|exists:locations,id',
            'selected_add_ons' => 'nullable|array',
            'selected_add_ons.*' => 'exists:plan_add_ons,id',
            'selected_one_time_fees' => 'nullable|array',
            'hay_credit_applied' => 'nullable|boolean',
            'is_byod' => 'nullable|boolean',
            'connection_fee_override' => 'nullable|numeric',
        ]);

        // Only admins can change team template status
        if (!Auth::user()->hasRole('admin')) {
            unset($validated['is_team_template']);
        }

        $contractTemplate->update($validated);

        return redirect()->route('contract-templates.index')
            ->with('success', 'Template updated successfully!');
    }

    /**
     * Remove the specified template
     */
    public function destroy(ContractTemplate $contractTemplate)
    {
        // Users can only delete their own templates, unless they're admin
        if ($contractTemplate->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $contractTemplate->delete();

        return redirect()->route('contract-templates.index')
            ->with('success', 'Template deleted successfully!');
    }

    /**
     * API: Get all available templates for current user (personal + team)
     */
    public function getTemplates()
    {
        $user = Auth::user();

        $templates = [
            'personal' => ContractTemplate::personal($user->id)
                ->with(['bellDevice', 'ratePlan', 'mobileInternetPlan', 'activityType', 'commitmentPeriod', 'location'])
                ->popular()
                ->get(),
            'team' => ContractTemplate::team()
                ->with(['bellDevice', 'ratePlan', 'mobileInternetPlan', 'activityType', 'commitmentPeriod', 'location'])
                ->popular()
                ->get(),
        ];

        return response()->json($templates);
    }

    /**
     * API: Get frequently used configurations from recent contracts
     * Analyzes last 90 days of contracts and returns most common configurations
     */
    public function getFrequentlyUsed()
    {
        // Get most common device + plan combinations from last 90 days
        $frequentlyUsed = Contract::where('created_at', '>=', now()->subDays(90))
            ->whereNotNull('bell_device_id')
            ->whereNotNull('rate_plan_id')
            ->select([
                'bell_device_id',
                'rate_plan_id',
                'mobile_internet_plan_id',
                'activity_type_id',
                'commitment_period_id',
                DB::raw('COUNT(*) as usage_count')
            ])
            ->groupBy([
                'bell_device_id',
                'rate_plan_id',
                'mobile_internet_plan_id',
                'activity_type_id',
                'commitment_period_id'
            ])
            ->having('usage_count', '>=', 2) // Only show if used at least twice
            ->orderBy('usage_count', 'desc')
            ->limit(10)
            ->get();

        // Manually load relationships for aggregated data
        $frequentlyUsed->each(function ($config) {
            $config->bell_device = \App\Models\BellDevice::find($config->bell_device_id);
            $config->rate_plan = \App\Models\RatePlan::find($config->rate_plan_id);
            $config->mobile_internet_plan = $config->mobile_internet_plan_id
                ? \App\Models\MobileInternetPlan::find($config->mobile_internet_plan_id)
                : null;
            $config->activity_type = $config->activity_type_id
                ? \App\Models\ActivityType::find($config->activity_type_id)
                : null;
            $config->commitment_period = $config->commitment_period_id
                ? \App\Models\CommitmentPeriod::find($config->commitment_period_id)
                : null;
        });

        // Filter out configurations where rate plan or device no longer exists/is inactive
        $frequentlyUsed = $frequentlyUsed->filter(function ($config) {
            // Must have a valid rate plan
            if (!$config->rate_plan || !$config->rate_plan->is_active) {
                return false;
            }

            // If has device, device must be valid and active
            if ($config->bell_device_id && (!$config->bell_device || !$config->bell_device->is_active)) {
                return false;
            }

            return true;
        })->values(); // Re-index array

        return response()->json($frequentlyUsed);
    }

    /**
     * API: Get template data for applying to contract form
     */
    public function getTemplateData(ContractTemplate $contractTemplate)
    {
        // Increment use count
        $contractTemplate->incrementUseCount();

        // Return full template data with relationships
        $contractTemplate->load([
            'bellDevice',
            'ratePlan',
            'mobileInternetPlan',
            'activityType',
            'commitmentPeriod',
            'location'
        ]);

        return response()->json($contractTemplate);
    }

    /**
     * Save current contract form as template (from contract create form)
     */
    public function saveFromContract(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_type_id' => 'nullable|exists:activity_types,id',
            'bell_device_id' => 'nullable|exists:bell_devices,id',
            'rate_plan_id' => 'nullable|exists:rate_plans,id',
            'mobile_internet_plan_id' => 'nullable|exists:mobile_internet_plans,id',
            'commitment_period_id' => 'nullable|exists:commitment_periods,id',
            'location_id' => 'nullable|exists:locations,id',
            'selected_add_ons' => 'nullable|array',
            'selected_one_time_fees' => 'nullable|array',
            'hay_credit_applied' => 'nullable|boolean',
            'is_byod' => 'nullable|boolean',
            'connection_fee_override' => 'nullable|numeric',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_team_template'] = false;

        $template = ContractTemplate::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully!',
            'template' => $template
        ]);
    }
}
