<?php

namespace App\Http\Controllers;

use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Models\PlanAddOn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\View\View;
use Carbon\Carbon;

class CellularPricingController extends Controller
{
    /**
     * Display the upload interface
     */
    public function upload(): View
    {
        return view('cellular-pricing.upload');
    }

    /**
     * Handle the file upload and trigger import
     */
	public function import(Request $request)
	{
		$request->validate([
			'pricing_file' => 'required|file|mimes:xlsx,xls|max:10240',
			'effective_date' => 'required|date',
			'replace' => 'sometimes|boolean',
		]);

		// Verify actual file content (magic bytes) to prevent file type spoofing
		$file = $request->file('pricing_file');
		if ($file) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mimeType = finfo_file($finfo, $file->getRealPath());
			finfo_close($finfo);

			$allowedMimeTypes = [
				'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
				'application/vnd.ms-excel', // .xls
				'application/zip', // .xlsx files may also be detected as zip
			];

			if (!in_array($mimeType, $allowedMimeTypes)) {
				return redirect()->route('cellular-pricing.upload')
					->with('error', 'Invalid file type. Only Excel files (.xlsx, .xls) are allowed.');
			}
		}

		$tempPath = null;

		try {
			// Get the uploaded file and move it to a temp location
			$file = $request->file('pricing_file');
			$tempPath = sys_get_temp_dir() . '/cellular_pricing_' . time() . '.' . $file->getClientOriginalExtension();
			
			// Move uploaded file to temp path
			$file->move(dirname($tempPath), basename($tempPath));

			// Verify file exists
			if (!file_exists($tempPath)) {
				return redirect()->route('cellular-pricing.upload')
					->with('error', 'Failed to save uploaded file. Please try again.');
			}

			// Run the import command
			$effectiveDate = $request->input('effective_date');
			$replace = $request->has('replace');

			\Illuminate\Support\Facades\Artisan::call('cellular:import-pricing', [
				'file' => $tempPath,
				'--date' => $effectiveDate,
				'--replace' => $replace,
			]);

			$output = \Illuminate\Support\Facades\Artisan::output();

			// Clean up the temporary file
			if (file_exists($tempPath)) {
				unlink($tempPath);
			}

			// Check if the command was successful
			if (strpos($output, 'Import completed successfully') !== false) {
				$ratePlans = RatePlan::where('effective_date', $effectiveDate)->count();
				$mobileInternet = MobileInternetPlan::where('effective_date', $effectiveDate)->count();
				$addOns = PlanAddOn::where('effective_date', $effectiveDate)->count();

				return redirect()->route('cellular-pricing.upload')
					->with('success', "Import successful! Imported {$ratePlans} rate plans, {$mobileInternet} mobile internet plans, and {$addOns} add-ons.");
			} else {
				return redirect()->route('cellular-pricing.upload')
					->with('error', 'Import output: ' . substr($output, 0, 1000));
			}
		} catch (\Exception $e) {
			// Clean up file if it exists
			if ($tempPath && file_exists($tempPath)) {
				unlink($tempPath);
			}
			
			return redirect()->route('cellular-pricing.upload')
				->with('error', 'Import failed: ' . $e->getMessage());
		}
	}    /**
     * Browse Rate Plans
     */
    public function ratePlans(Request $request): View
    {
        $query = RatePlan::current()->active();

        // Filter by plan type
        if ($request->filled('plan_type')) {
            $query->ofType($request->input('plan_type'));
        }

        // Filter by tier
        if ($request->filled('tier')) {
            $query->ofTier($request->input('tier'));
        }

        // Search by name or SOC code
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('plan_name', 'LIKE', "%{$search}%")
                  ->orWhere('soc_code', 'LIKE', "%{$search}%");
            });
        }

        $plans = $query->orderBy('plan_type')
            ->orderBy('tier')
            ->orderBy('base_price')
            ->paginate(20)
            ->appends($request->query());

        $tiers = ['Lite', 'Select', 'Max', 'Ultra'];

        return view('cellular-pricing.rate-plans', compact('plans', 'tiers'));
    }

    /**
     * Show Rate Plan details
     */
    public function ratePlanShow($id): View
    {
        $plan = RatePlan::findOrFail($id);
        
        // Get pricing history
        $history = RatePlan::where('soc_code', $plan->soc_code)
            ->where('id', '!=', $plan->id)
            ->orderBy('effective_date', 'desc')
            ->get();

        return view('cellular-pricing.rate-plan-show', compact('plan', 'history'));
    }

    /**
     * Browse Mobile Internet Plans
     */
    public function mobileInternet(Request $request): View
    {
        $query = MobileInternetPlan::current()->active();

        // Filter by category
        if ($request->filled('category')) {
            $query->ofCategory($request->input('category'));
        }

        // Search by name or SOC code
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('plan_name', 'LIKE', "%{$search}%")
                  ->orWhere('soc_code', 'LIKE', "%{$search}%");
            });
        }

        $plans = $query->orderBy('monthly_rate')
            ->paginate(20)
            ->appends($request->query());

        // Get unique categories for filter
        $categories = MobileInternetPlan::current()
            ->active()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        return view('cellular-pricing.mobile-internet', compact('plans', 'categories'));
    }

    /**
     * Browse Plan Add-Ons
     */
    public function addOns(Request $request): View
    {
        $query = PlanAddOn::current()->active();

        // Filter by category
        if ($request->filled('category')) {
            $query->ofCategory($request->input('category'));
        }

        // Search by name or SOC code
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('add_on_name', 'LIKE', "%{$search}%")
                  ->orWhere('soc_code', 'LIKE', "%{$search}%");
            });
        }

        $addOns = $query->orderBy('category')
            ->orderBy('add_on_name')
            ->paginate(20)
            ->appends($request->query());

        // Get unique categories for filter
        $categories = PlanAddOn::current()
            ->active()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort();

        return view('cellular-pricing.add-ons', compact('addOns', 'categories'));
    }

    /**
     * Compare Rate Plans
     */
    public function compare(Request $request): View
    {
        $planIds = $request->input('plans', []);
        
        $plans = RatePlan::whereIn('id', $planIds)
            ->where('is_current', true)
            ->get();

        return view('cellular-pricing.compare', compact('plans'));
    }

    /**
     * API endpoint to get pricing for a specific plan
     */
    public function getPricing(Request $request)
    {
        $socCode = $request->input('soc_code');
        $tier = $request->input('tier');

        if (!$socCode) {
            return response()->json(['error' => 'SOC code is required'], 400);
        }

        $plan = RatePlan::getPricing($socCode, $tier);

        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }

        return response()->json([
            'plan' => [
                'id' => $plan->id,
                'soc_code' => $plan->soc_code,
                'plan_name' => $plan->plan_name,
                'plan_type' => $plan->plan_type,
                'tier' => $plan->tier,
                'base_price' => $plan->base_price,
                'promo_price' => $plan->promo_price,
                'effective_price' => $plan->effective_price,
                'promo_description' => $plan->promo_description,
                'data_amount' => $plan->data_amount,
                'is_international' => $plan->is_international,
                'is_us_mexico' => $plan->is_us_mexico,
            ]
        ]);
    }

    /**
     * API endpoint to get mobile internet pricing
     */
    public function getMobileInternetPricing(Request $request)
    {
        $socCode = $request->input('soc_code');

        if (!$socCode) {
            return response()->json(['error' => 'SOC code is required'], 400);
        }

        $plan = MobileInternetPlan::getPricing($socCode);

        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }

        return response()->json([
            'plan' => [
                'id' => $plan->id,
                'soc_code' => $plan->soc_code,
                'plan_name' => $plan->plan_name,
                'monthly_rate' => $plan->monthly_rate,
                'category' => $plan->category,
                'promo_group' => $plan->promo_group,
            ]
        ]);
    }

    /**
     * API endpoint to get add-on pricing
     */
    public function getAddOnPricing(Request $request)
    {
        $socCode = $request->input('soc_code');

        if (!$socCode) {
            return response()->json(['error' => 'SOC code is required'], 400);
        }

        $addOn = PlanAddOn::getPricing($socCode);

        if (!$addOn) {
            return response()->json(['error' => 'Add-on not found'], 404);
        }

        return response()->json([
            'add_on' => [
                'id' => $addOn->id,
                'soc_code' => $addOn->soc_code,
                'add_on_name' => $addOn->add_on_name,
                'monthly_rate' => $addOn->monthly_rate,
                'category' => $addOn->category,
            ]
        ]);
    }
}