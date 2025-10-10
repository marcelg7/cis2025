<?php

namespace App\Http\Controllers;

use App\Models\BellDevice;
use App\Models\BellPricing;
use App\Models\BellDroPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BellPricingController extends Controller
{
    public function index(Request $request): View
    {
        $query = BellDevice::with(['currentPricing', 'currentDroPricing'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('manufacturer')) {
            $query->where('manufacturer', $request->input('manufacturer'));
        }

        $devices = $query->orderBy('manufacturer')->orderBy('model')->paginate(20);
        
        $manufacturers = BellDevice::where('is_active', true)
            ->distinct()
            ->pluck('manufacturer')
            ->filter()
            ->sort()
            ->values();

        return view('bell-pricing.index', compact('devices', 'manufacturers'));
    }

    public function show($id): View
    {
        $device = BellDevice::with(['currentPricing', 'currentDroPricing'])
            ->findOrFail($id);

        return view('bell-pricing.show', compact('device'));
    }

    public function history($id): View
    {
        $device = BellDevice::with(['pricing' => function ($query) {
            $query->orderBy('effective_date', 'desc');
        }, 'droPricing' => function ($query) {
            $query->orderBy('effective_date', 'desc');
        }])->findOrFail($id);

        return view('bell-pricing.history', compact('device'));
    }

	public function compare(Request $request): View
	{
		// Get all devices for the selection form
		$allDevices = BellDevice::where('is_active', true)
			->orderBy('manufacturer')
			->orderBy('model')
			->get();

		// Get selected devices if any
		$deviceIds = $request->input('devices', []);
		$tier = $request->input('tier', 'Select');
		$pricingType = $request->input('pricing_type', 'smartpay');

		// Always return a collection, even if empty
		if (!empty($deviceIds) && is_array($deviceIds)) {
			$selectedDevices = BellDevice::whereIn('id', $deviceIds)
				->with($pricingType === 'dro' ? 'currentDroPricing' : 'currentPricing')
				->get();
		} else {
			$selectedDevices = collect(); // Empty collection
		}

		return view('bell-pricing.compare', compact('allDevices', 'selectedDevices', 'tier', 'pricingType'));
	}

    public function uploadForm(): View
    {
        return view('bell-pricing.upload');
    }

	public function upload(Request $request)
	{
		$request->validate([
			'pricing_file' => 'required|file|mimes:xlsx,xls|max:10240',
			'effective_date' => 'required|date',
		]);

		try {
			// Increase limits for large imports
			ini_set('max_execution_time', 300); // 5 minutes
			ini_set('memory_limit', '512M');

			// Ensure temp directory exists
			$tempDir = storage_path('app/temp');
			if (!file_exists($tempDir)) {
				mkdir($tempDir, 0755, true);
			}

			// Store the uploaded file temporarily
			$file = $request->file('pricing_file');
			$filename = 'bell-pricing-' . time() . '.' . $file->getClientOriginalExtension();
			
			// Use move instead of storeAs for more reliability
			$fullPath = $tempDir . '/' . $filename;
			$file->move($tempDir, $filename);

			Log::info('Starting pricing upload', [
				'filename' => $filename,
				'path' => $fullPath,
				'file_exists' => file_exists($fullPath),
				'effective_date' => $request->input('effective_date'),
			]);

			// Verify file exists before running command
			if (!file_exists($fullPath)) {
				throw new \Exception("File was not uploaded successfully to: {$fullPath}");
			}

			// Run the import command with --replace flag
			try {
				$exitCode = Artisan::call('bell:import-pricing', [
					'file' => $fullPath,
					'--date' => $request->input('effective_date'),
					'--replace' => true, // Always replace when uploading via web
				]);

				// Get the output
				$output = Artisan::output();

				Log::info('Import command completed', [
					'exit_code' => $exitCode,
					'output' => $output,
				]);
			} catch (\Exception $e) {
				Log::error('Import command threw exception', [
					'error' => $e->getMessage(),
					'trace' => $e->getTraceAsString(),
				]);
				throw $e;
			}

			// Clean up temp file
			if (file_exists($fullPath)) {
				unlink($fullPath);
			}

			// Check if import was successful (exit code 0 means success)
			if ($exitCode === 0) {
				// Parse the output to get record counts
				preg_match('/SmartPay records: (\d+)/', $output, $smartPayMatches);
				preg_match('/DRO records: (\d+)/', $output, $droMatches);
				
				$smartPayCount = $smartPayMatches[1] ?? 'unknown';
				$droCount = $droMatches[1] ?? 'unknown';
				
				return redirect()->route('bell-pricing.index')
					->with('success', "Pricing imported successfully! SmartPay: {$smartPayCount} records, DRO: {$droCount} records. Previous pricing for this date was replaced.");
			} else {
				Log::error('Import failed with exit code', [
					'exit_code' => $exitCode,
					'output' => $output,
				]);
				
				return redirect()->back()
					->with('error', 'Import failed. ' . strip_tags($output))
					->withInput();
			}

		} catch (\Exception $e) {
			Log::error('Pricing upload failed', [
				'error' => $e->getMessage(),
				'trace' => $e->getTraceAsString()
			]);

			return redirect()->back()
				->with('error', 'Upload failed: ' . $e->getMessage())
				->withInput();
		}
	}

    /**
     * API endpoint to get pricing for a specific device and tier
     */
    public function getPricing(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:bell_devices,id',
            'tier' => 'required|in:Ultra,Max,Select,Lite',
            'pricing_type' => 'in:smartpay,dro',
        ]);

        $deviceId = $request->input('device_id');
        $tier = $request->input('tier');
        $pricingType = $request->input('pricing_type', 'smartpay');

        if ($pricingType === 'dro') {
            $pricing = BellDroPricing::getPricing($deviceId, $tier);
        } else {
            $pricing = BellPricing::getPricing($deviceId, $tier);
        }

        if (!$pricing) {
            return response()->json(['error' => 'Pricing not found'], 404);
        }

        return response()->json($pricing);
    }

    /**
     * API endpoint to get device pricing details
     */
    public function getDevicePricing(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:bell_devices,id',
            'tier' => 'required|in:Ultra,Max,Select,Lite',
            'pricing_type' => 'required|in:smartpay,dro',
        ]);

        $deviceId = $request->input('device_id');
        $tier = $request->input('tier');
        $pricingType = $request->input('pricing_type');

        $device = BellDevice::findOrFail($deviceId);

        if ($pricingType === 'dro') {
            $pricing = BellDroPricing::getPricing($deviceId, $tier);
        } else {
            $pricing = BellPricing::getPricing($deviceId, $tier);
        }

        if (!$pricing) {
            return response()->json(['error' => 'Pricing not found'], 404);
        }

        return response()->json([
            'device' => $device,
            'pricing' => $pricing,
        ]);
    }
}