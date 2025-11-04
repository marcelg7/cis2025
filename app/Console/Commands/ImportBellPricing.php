<?php

namespace App\Console\Commands;

use App\Models\BellDevice;
use App\Models\BellPricing;
use App\Models\BellDroPricing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportBellPricing extends Command
{
    protected $signature = 'bell:import-pricing {file} {--date= : Effective date (YYYY-MM-DD)} {--replace : Replace existing pricing for this date}';
    protected $description = 'Import Bell pricing from Excel file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $effectiveDate = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::today();

        $replace = $this->option('replace') || $this->confirm(
            "Do you want to replace existing pricing for {$effectiveDate->format('Y-m-d')}?",
            true // Default to yes
        );

        $this->info("Importing Bell pricing with effective date: {$effectiveDate->format('Y-m-d')}");
        if ($replace) {
            $this->info("Existing pricing for this date will be replaced.");
        }

        try {
            DB::beginTransaction();

            $spreadsheet = IOFactory::load($filePath);

            // List available sheets for debugging
            $sheetNames = [];
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $sheetNames[] = $sheet->getTitle();
            }
            $this->info('Available sheets in file: ' . implode(', ', $sheetNames));

            // Import SmartPay pricing (Ultra, Max, Select, Lite)
            $this->info('Importing SmartPay pricing (Ultra/Max/Select/Lite)...');
            $smartPaySheet = $spreadsheet->getSheetByName('SMART PAY');
            $smartPayCount = $this->importSmartPay($smartPaySheet, $effectiveDate, $replace);

            // Import SmartPay Basic pricing
            $smartPayBasicCount = 0;
            $smartPayBasicSheet = $spreadsheet->getSheetByName('SMART PAY BASIC');

            if ($smartPayBasicSheet !== null) {
                try {
                    $this->info('Importing SmartPay Basic pricing...');
                    // Use the separate Basic import function with correct column mapping
                    $smartPayBasicCount = $this->importSmartPayBasic($smartPayBasicSheet, $effectiveDate, $replace);
                } catch (\Exception $e) {
                    $this->warn('Error importing SmartPay Basic: ' . $e->getMessage());
                }
            } else {
                $this->warn('SMART PAY BASIC sheet not found in spreadsheet - skipping');
            }

            // Import DRO pricing
            $this->info('Importing DRO pricing...');
            $droSheet = $spreadsheet->getSheetByName('DRO - SMARTPAY');
            $droCount = $this->importDro($droSheet, $effectiveDate, $replace);

            // Mark devices not in this import as inactive
            $allDeviceNames = array_unique(array_merge(
                $this->getDeviceNamesFromSheet($smartPaySheet),
                $smartPayBasicSheet ? $this->getDeviceNamesFromSheet($smartPayBasicSheet) : [],
                $this->getDeviceNamesFromSheet($droSheet)
            ));

            $this->info('Found ' . count($allDeviceNames) . ' unique devices in spreadsheet');

            // Mark devices NOT in the import as inactive
            $deactivatedCount = BellDevice::whereNotIn('name', $allDeviceNames)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            if ($deactivatedCount > 0) {
                $this->info("Marked {$deactivatedCount} devices as inactive (removed from spreadsheet)");
            }

            DB::commit();

            $this->info("Import completed successfully!");
            $this->info("SmartPay records (Ultra/Max/Select/Lite): {$smartPayCount}");
            $this->info("SmartPay Basic records: {$smartPayBasicCount}");
            $this->info("DRO records: {$droCount}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Import failed: " . $e->getMessage());
            Log::error('Bell pricing import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }

	private function importSmartPay($sheet, $effectiveDate, $replace): int
	{
		$rows = $sheet->toArray();
		$count = 0;
		$skipped = 0;

		$this->info("Total rows in SmartPay sheet: " . count($rows));
		$this->info("First 10 rows:");
		for ($i = 0; $i < min(10, count($rows)); $i++) {
			$this->info("Row {$i}: " . ($rows[$i][0] ?? 'empty'));
		}

		// If replacing, delete existing records for this effective date first
		if ($replace) {
			$deleted = BellPricing::where('effective_date', $effectiveDate)->delete();
			$this->info("Deleted {$deleted} existing SmartPay records for {$effectiveDate->format('Y-m-d')}");
		}

		// Mark all previous records as not current (only if not same date)
		BellPricing::where('is_current', true)
			->where('effective_date', '!=', $effectiveDate)
			->update(['is_current' => false]);

		// Start from row 4 (index 3 is headers, index 4+ is data)
		for ($rowIndex = 4; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];
			
			// Skip if device name or tier is empty
			if (empty($row[0]) || empty($row[4])) {
				$skipped++;
				continue;
			}

			// Validate tier is one of the expected values
			$tier = trim($row[4]);
			if (!in_array($tier, ['Ultra', 'Max', 'Select', 'Lite', 'Basic'])) {
				if ($rowIndex < 10) {
					$this->warn("Row {$rowIndex}: Invalid tier '{$tier}' for device '{$row[0]}' - skipping");
				}
				$skipped++;
				continue;
			}

			$deviceName = trim($row[0]);

			// Clean and parse the retail price
			$retailPrice = $this->parseDecimal($row[1]);

			// Skip if retail price is missing or invalid
			if ($retailPrice <= 0) {
				if ($rowIndex < 10) {
					$this->warn("Row {$rowIndex}: Invalid retail price '{$retailPrice}' for device '{$deviceName}' - skipping");
				}
				$skipped++;
				continue;
			}

			// Debug first few data rows
			if ($rowIndex < 8) {
				$this->info("Processing row {$rowIndex}: Device='{$deviceName}', Tier='{$tier}', Price='{$retailPrice}'");
			}

			try {
				// Create or get device
				$device = $this->getOrCreateDevice($deviceName);

				// Create pricing record
				BellPricing::create([
					'bell_device_id' => $device->id,
					'tier' => $tier,
					'retail_price' => $retailPrice,
					'upfront_payment' => $this->parseDecimal($row[2]),
					'agreement_credit' => $this->parseDecimal($row[3]),
					'plan_cost' => $this->parseDecimal($row[5]),
					'monthly_device_cost_pre_tax' => $this->parseDecimal($row[6]),
					'monthly_device_cost_with_hst' => $this->parseDecimal($row[7]),
					'plan_plus_device_pre_tax' => $this->parseDecimal($row[8]),
					'plan_with_10_hay_credit' => $this->parseDecimal($row[9]),
					'hay_credit_plus_device_pre_tax' => $this->parseDecimal($row[10]),
					'plan_with_15_aal' => $this->parseDecimal($row[11]),
					'aal_15_plan_plus_device_pre_tax' => $this->parseDecimal($row[12]),
					'plan_with_30_aal' => $this->parseDecimal($row[13]),
					'aal_30_plan_plus_device_pre_tax' => $this->parseDecimal($row[14]),
					'plan_with_40_aal' => $this->parseDecimal($row[15]),
					'aal_40_plan_plus_device_pre_tax' => $this->parseDecimal($row[16]),
					'effective_date' => $effectiveDate,
					'is_current' => true,
				]);

				$count++;
			} catch (\Exception $e) {
				$this->warn("Row {$rowIndex}: Error importing '{$deviceName}' ({$tier}) - " . $e->getMessage());
				$skipped++;
			}
		}

		if ($skipped > 0) {
			$this->info("Skipped {$skipped} rows");
		}

		return $count;
	}

	private function importSmartPayBasic($sheet, $effectiveDate, $replace): int
	{
		$rows = $sheet->toArray();
		$count = 0;
		$skipped = 0;

		$this->info("Total rows in SmartPay Basic sheet: " . count($rows));

		// If replacing, delete existing Basic records for this effective date
		if ($replace) {
			$deleted = BellPricing::where('effective_date', $effectiveDate)
				->where('tier', 'Basic')
				->delete();
			$this->info("Deleted {$deleted} existing SmartPay Basic records for {$effectiveDate->format('Y-m-d')}");
		}

		// Mark all previous Basic records as not current (only if not same date)
		BellPricing::where('is_current', true)
			->where('tier', 'Basic')
			->where('effective_date', '!=', $effectiveDate)
			->update(['is_current' => false]);

		// Start from row 7 (skip headers at row 5 and junk row at row 6)
		for ($rowIndex = 7; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];

			// Skip if device name is empty
			if (empty($row[0])) {
				$skipped++;
				continue;
			}

			$deviceName = trim($row[0]);

			// Parse the retail price
			$retailPrice = $this->parseDecimal($row[1]);

			// Skip if retail price is missing or invalid
			if ($retailPrice <= 0) {
				if ($rowIndex < 12) {
					$this->warn("Row {$rowIndex}: Invalid retail price '{$retailPrice}' for device '{$deviceName}' - skipping");
				}
				$skipped++;
				continue;
			}

			// Debug first few data rows
			if ($rowIndex < 12) {
				$this->info("Processing row {$rowIndex}: Device='{$deviceName}', Tier='Basic', Price='{$retailPrice}'");
			}

			try {
				// Create or get device
				$device = $this->getOrCreateDevice($deviceName);

				// SMART PAY BASIC column structure:
				// [0]=Device, [1]=Retail, [2]=Upfront, [3]=Agreement Credit,
				// [4]=Monthly Payment Pre-Tax, [5]=null, [6]=$0 Down total, [7]=Plan Cost

				$planCost = $this->parseDecimal($row[7]);
				$monthlyDevicePreTax = $this->parseDecimal($row[4]);
				$monthlyDeviceWithHst = $monthlyDevicePreTax * 1.13; // Calculate HST
				$planPlusDevicePreTax = $planCost + $monthlyDevicePreTax;

				// Create pricing record with tier='Basic'
				BellPricing::create([
					'bell_device_id' => $device->id,
					'tier' => 'Basic',
					'retail_price' => $retailPrice,
					'upfront_payment' => $this->parseDecimal($row[2]),
					'agreement_credit' => $this->parseDecimal($row[3]),
					'plan_cost' => $planCost,
					'monthly_device_cost_pre_tax' => $monthlyDevicePreTax,
					'monthly_device_cost_with_hst' => $monthlyDeviceWithHst,
					'plan_plus_device_pre_tax' => $planPlusDevicePreTax,
					'plan_with_10_hay_credit' => 0, // Not applicable for Basic
					'hay_credit_plus_device_pre_tax' => 0,
					'plan_with_15_aal' => 0,
					'aal_15_plan_plus_device_pre_tax' => 0,
					'plan_with_30_aal' => 0,
					'aal_30_plan_plus_device_pre_tax' => 0,
					'plan_with_40_aal' => 0,
					'aal_40_plan_plus_device_pre_tax' => 0,
					'effective_date' => $effectiveDate,
					'is_current' => true,
				]);

				$count++;
			} catch (\Exception $e) {
				$this->warn("Row {$rowIndex}: Error importing '{$deviceName}' (Basic) - " . $e->getMessage());
				$skipped++;
			}
		}

		if ($skipped > 0) {
			$this->info("Skipped {$skipped} rows in Basic sheet");
		}

		return $count;
	}

	private function importDro($sheet, $effectiveDate, $replace): int
	{
		$rows = $sheet->toArray();
		$count = 0;
		$skipped = 0;

		// If replacing, delete existing records for this effective date first
		if ($replace) {
			$deleted = BellDroPricing::where('effective_date', $effectiveDate)->delete();
			$this->info("Deleted {$deleted} existing DRO records for {$effectiveDate->format('Y-m-d')}");
		}

		// Mark all previous records as not current (only if not same date)
		BellDroPricing::where('is_current', true)
			->where('effective_date', '!=', $effectiveDate)
			->update(['is_current' => false]);

		// Start from row 6 (index 5 is headers, index 6+ is data)
		for ($rowIndex = 6; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];
			
			// Skip if device name or tier is empty
			if (empty($row[0]) || empty($row[5])) {
				$skipped++;
				continue;
			}

			// Validate tier is one of the expected values
			$tier = trim($row[5]);
			if (!in_array($tier, ['Ultra', 'Max', 'Select', 'Lite', 'Basic'])) {
				if ($rowIndex < 10) {
					$this->warn("Row {$rowIndex}: Invalid tier '{$tier}' for device '{$row[0]}' - skipping");
				}
				$skipped++;
				continue;
			}

			$deviceName = trim($row[0]);

			// Clean and parse the retail price
			$retailPrice = $this->parseDecimal($row[1]);

			// Skip if retail price is missing or invalid
			if ($retailPrice <= 0) {
				if ($rowIndex < 10) {
					$this->warn("Row {$rowIndex}: Invalid retail price '{$retailPrice}' for device '{$deviceName}' - skipping");
				}
				$skipped++;
				continue;
			}

			try {
				// Create or get device
				$device = $this->getOrCreateDevice($deviceName);

				// Create DRO pricing record
				BellDroPricing::create([
					'bell_device_id' => $device->id,
					'tier' => $tier,
					'retail_price' => $retailPrice,
					'upfront_payment' => $this->parseDecimal($row[2]),
					'agreement_credit' => $this->parseDecimal($row[3]),
					'dro_amount' => $this->parseDecimal($row[4]),
					'plan_cost' => $this->parseDecimal($row[6]),
					'monthly_device_cost_pre_tax' => $this->parseDecimal($row[7]),
					'monthly_device_cost_with_hst' => $this->parseDecimal($row[8]),
					'plan_plus_device_pre_tax' => $this->parseDecimal($row[9]),
					'plan_with_10_hay_credit' => $this->parseDecimal($row[10]),
					'hay_credit_plus_device_pre_tax' => $this->parseDecimal($row[11]),
					'plan_with_15_aal' => $this->parseDecimal($row[12]),
					'aal_15_plan_plus_device_pre_tax' => $this->parseDecimal($row[13]),
					'plan_with_30_aal' => $this->parseDecimal($row[14]),
					'aal_30_plan_plus_device_pre_tax' => $this->parseDecimal($row[15]),
					'plan_with_40_aal' => $this->parseDecimal($row[16]),
					'aal_40_plan_plus_device_pre_tax' => $this->parseDecimal($row[17]),
					'effective_date' => $effectiveDate,
					'is_current' => true,
				]);

				$count++;
			} catch (\Exception $e) {
				$this->warn("Row {$rowIndex}: Error importing '{$deviceName}' ({$tier}) - " . $e->getMessage());
				$skipped++;
			}
		}

		if ($skipped > 0) {
			$this->info("Skipped {$skipped} rows");
		}

		return $count;
	}

    private function getOrCreateDevice(string $name): BellDevice
    {
        $device = BellDevice::where('name', $name)->first();

        if (!$device) {
            $parsed = BellDevice::parseDeviceName($name);
            $device = BellDevice::create([
                'name' => $name,
                'manufacturer' => $parsed['manufacturer'],
                'model' => $parsed['model'],
                'storage' => $parsed['storage'],
                'is_active' => true,
            ]);
        }

        return $device;
    }

	private function parseDecimal($value): float
	{
		if (is_null($value) || $value === '') {
			return 0.00;
		}

		// Remove dollar signs, commas, and other non-numeric characters except decimal point
		$cleanValue = preg_replace('/[^0-9.]/', '', $value);

		// If empty after cleaning, return 0
		if ($cleanValue === '') {
			return 0.00;
		}

		return (float) $cleanValue;
	}

	private function getDeviceNamesFromSheet($sheet): array
	{
		$rows = $sheet->toArray();
		$deviceNames = [];

		// Start from row 4 for SmartPay sheets (same logic as import)
		for ($rowIndex = 4; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];

			// Skip if device name is empty
			if (!empty($row[0])) {
				$deviceNames[] = trim($row[0]);
			}
		}

		return $deviceNames;
	}
}