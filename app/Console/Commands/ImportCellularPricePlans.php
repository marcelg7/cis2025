<?php

namespace App\Console\Commands;

use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Models\PlanAddOn;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class ImportCellularPricePlans extends Command
{
    protected $signature = 'cellular:import-pricing {file} {--date= : Effective date (Y-m-d format)} {--replace : Replace existing data for this date}';
    
    protected $description = 'Import cellular pricing from Excel file (Rate Plans, Mobile Internet, and Add-Ons)';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        // Get effective date
        $effectiveDate = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::today();
        
        $replace = $this->option('replace');

        $this->info("Importing Cellular Price Plans...");
        $this->info("Effective Date: {$effectiveDate->format('Y-m-d')}");
        
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            // Import Rate Plans
            $this->info("\n📱 Importing Rate Plan Overview...");
            $ratePlanCount = $this->importRatePlans($spreadsheet, $effectiveDate, $replace);
            $this->info("✓ Imported {$ratePlanCount} rate plans");
            
            // Import Mobile Internet Plans
            $this->info("\n📡 Importing Mobile Internet Plans...");
            $mobileInternetCount = $this->importMobileInternet($spreadsheet, $effectiveDate, $replace);
            $this->info("✓ Imported {$mobileInternetCount} mobile internet plans");
            
            // Import Plan Add-Ons
            $this->info("\n➕ Importing Plan Add-Ons...");
            $addOnCount = $this->importPlanAddOns($spreadsheet, $effectiveDate, $replace);
            $this->info("✓ Imported {$addOnCount} plan add-ons");
            
            $this->info("\n✅ Import completed successfully!");
            $this->info("Total imported: {$ratePlanCount} rate plans, {$mobileInternetCount} mobile internet plans, {$addOnCount} add-ons");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Import failed: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }


	private function importRatePlans($spreadsheet, $effectiveDate, $replace): int
	{
		$sheet = $spreadsheet->getSheetByName('Rate Plan Overview');
		if (!$sheet) {
			$this->warn("Sheet 'Rate Plan Overview' not found, skipping...");
			return 0;
		}

		$rows = $sheet->toArray();
		$count = 0;
		$skipped = 0;

		$this->info("Total rows in Rate Plan sheet: " . count($rows));

		// DON'T delete when replacing - we'll update instead to preserve custom fields
		// if ($replace) {
		//     $deleted = RatePlan::where('effective_date', $effectiveDate)->delete();
		//     $this->info("Deleted {$deleted} existing rate plans for {$effectiveDate->format('Y-m-d')}");
		// }

		// Mark all previous records as not current (only if not same date)
		RatePlan::where('is_current', true)
			->where('effective_date', '!=', $effectiveDate)
			->update(['is_current' => false]);

		// Start from row 8
		for ($rowIndex = 8; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];
			
			// Process BYOD plans (columns 1-5: index 1-5)
			if (!empty($row[1]) && !empty($row[2]) && isset($row[3])) {
				$socCode = trim($row[1]);
				$planName = trim($row[2]);
				$basePrice = $this->parseDecimal($row[3]);
				
				if ($basePrice > 0) {
					if ($rowIndex < 13) {
						$this->info("Row {$rowIndex}: BYOD plan: {$socCode} - {$planName} @ \${$basePrice}");
					}
					
					$promoDescription = isset($row[5]) && !empty(trim($row[5])) ? trim($row[5]) : null;
					$tier = $this->extractTier($planName);
					$promoPrice = $this->extractPromoPrice($promoDescription);
					$isInternational = stripos($planName, 'INT') !== false;
					$isUsMexico = stripos($planName, 'US') !== false || stripos($planName, 'MX') !== false;
					$dataAmount = $this->extractDataAmount($planName);
					
					try {
						// Check if plan already exists
						$existingPlan = RatePlan::where('soc_code', $socCode)
							->where('effective_date', $effectiveDate)
							->first();
						
						$planData = [
							'plan_name' => $planName,
							'plan_type' => 'byod',
							'tier' => $tier,
							'base_price' => $basePrice,
							'promo_price' => $promoPrice,
							'promo_description' => $promoDescription,
							'data_amount' => $dataAmount,
							'is_international' => $isInternational,
							'is_us_mexico' => $isUsMexico,
							'effective_date' => $effectiveDate,
							'is_current' => true,
							'is_active' => empty($row[0]) || $row[0] !== 'X',
						];
						
						// PRESERVE custom features field if it exists
						if ($existingPlan && !empty($existingPlan->features)) {
							$this->info("  → Preserving custom features for {$socCode}");
							$planData['features'] = $existingPlan->features;
						}
						
						// Use updateOrCreate to preserve custom fields
						RatePlan::updateOrCreate(
							[
								'soc_code' => $socCode,
								'effective_date' => $effectiveDate
							],
							$planData
						);
						
						$count++;
					} catch (\Exception $e) {
						$this->warn("Row {$rowIndex}: Error importing BYOD plan '{$planName}' - " . $e->getMessage());
						$skipped++;
					}
				}
			}
			
			// Process SmartPay plans (columns 7-12: index 7-12)
			if (!empty($row[7]) && !empty($row[8]) && isset($row[9])) {
				$socCode = trim($row[7]);
				$planName = trim($row[8]);
				$basePrice = $this->parseDecimal($row[9]);
				
				if ($basePrice > 0) {
					if ($rowIndex < 13) {
						$this->info("Row {$rowIndex}: SmartPay plan: {$socCode} - {$planName} @ \${$basePrice}");
					}
					
					$tier = isset($row[10]) && !empty(trim($row[10])) ? trim($row[10]) : null;
					$promoDescription = isset($row[12]) && !empty(trim($row[12])) ? trim($row[12]) : null;
					$promoPrice = $this->extractPromoPrice($promoDescription);
					$isInternational = stripos($planName, 'INT') !== false;
					$isUsMexico = stripos($planName, 'US') !== false || stripos($planName, 'MX') !== false;
					$dataAmount = $this->extractDataAmount($planName);
					
					try {
						// Check if plan already exists
						$existingPlan = RatePlan::where('soc_code', $socCode)
							->where('effective_date', $effectiveDate)
							->first();
						
						$planData = [
							'plan_name' => $planName,
							'plan_type' => 'smartpay',
							'tier' => $tier,
							'base_price' => $basePrice,
							'promo_price' => $promoPrice,
							'promo_description' => $promoDescription,
							'data_amount' => $dataAmount,
							'is_international' => $isInternational,
							'is_us_mexico' => $isUsMexico,
							'effective_date' => $effectiveDate,
							'is_current' => true,
							'is_active' => empty($row[6]) || $row[6] !== 'X',
						];
						
						// PRESERVE custom features field if it exists
						if ($existingPlan && !empty($existingPlan->features)) {
							$this->info("  → Preserving custom features for {$socCode}");
							$planData['features'] = $existingPlan->features;
						}
						
						// Use updateOrCreate to preserve custom fields
						RatePlan::updateOrCreate(
							[
								'soc_code' => $socCode,
								'effective_date' => $effectiveDate
							],
							$planData
						);
						
						$count++;
					} catch (\Exception $e) {
						$this->warn("Row {$rowIndex}: Error importing SmartPay plan '{$planName}' - " . $e->getMessage());
						$skipped++;
					}
				}
			}
		}

		if ($skipped > 0) {
			$this->info("Skipped {$skipped} rows");
		}

		return $count;
	}


	private function importMobileInternet($spreadsheet, $effectiveDate, $replace): int
	{
		$sheet = $spreadsheet->getSheetByName('Mobile Internet');
		if (!$sheet) {
			$this->warn("Sheet 'Mobile Internet' not found, skipping...");
			return 0;
		}

		$rows = $sheet->toArray();
		$count = 0;
		$skipped = 0;

		$this->info("Total rows in Mobile Internet sheet: " . count($rows));

		// DON'T delete - we'll update instead to preserve custom fields
		// Mark all previous records as not current
		MobileInternetPlan::where('is_current', true)
			->where('effective_date', '!=', $effectiveDate)
			->update(['is_current' => false]);

		for ($rowIndex = 4; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];
			
			if (empty($row[0]) || empty($row[2])) {
				continue;
			}
			
			$socCode = trim($row[0]);
			$planName = trim($row[1]);
			$monthlyRate = $this->parseDecimal($row[2]);
			
			if ($monthlyRate <= 0) {
				continue;
			}
			
			$category = isset($row[3]) ? trim($row[3]) : null;
			$promoGroup = isset($row[4]) ? trim($row[4]) : null;
			
			try {
				// Check if plan already exists
				$existingPlan = MobileInternetPlan::where('soc_code', $socCode)
					->where('effective_date', $effectiveDate)
					->first();
				
				$planData = [
					'plan_name' => $planName,
					'monthly_rate' => $monthlyRate,
					'category' => $category,
					'promo_group' => $promoGroup,
					'effective_date' => $effectiveDate,
					'is_current' => true,
				];
				
				// PRESERVE custom description field if it exists
				if ($existingPlan && !empty($existingPlan->description)) {
					$this->info("  → Preserving custom description for {$socCode}");
					$planData['description'] = $existingPlan->description;
				}
				
				MobileInternetPlan::updateOrCreate(
					[
						'soc_code' => $socCode,
						'effective_date' => $effectiveDate
					],
					$planData
				);
				
				$count++;
			} catch (\Exception $e) {
				$this->warn("Row {$rowIndex}: Error importing mobile internet plan '{$planName}' - " . $e->getMessage());
				$skipped++;
			}
		}

		if ($skipped > 0) {
			$this->info("Skipped {$skipped} rows");
		}

		return $count;
	}



	private function importPlanAddOns($spreadsheet, $effectiveDate, $replace): int
	{
		$sheet = $spreadsheet->getSheetByName('Plan Add-Ons');
		if (!$sheet) {
			$this->warn("Sheet 'Plan Add-Ons' not found, skipping...");
			return 0;
		}

		$rows = $sheet->toArray();
		$count = 0;
		$skipped = 0;

		$this->info("Total rows in Plan Add-Ons sheet: " . count($rows));
		$this->info("Examining rows 0-10:");
		for ($i = 0; $i <= 10 && $i < count($rows); $i++) {
			$row = $rows[$i];
			$this->info("Row {$i}: Col0='" . ($row[0] ?? 'EMPTY') . "', Col1='" . ($row[1] ?? 'EMPTY') . "', Col2='" . ($row[2] ?? 'EMPTY') . "'");
		}

		// If replacing, delete existing records for this effective date first
		if ($replace) {
			$deleted = PlanAddOn::where('effective_date', $effectiveDate)->delete();
			$this->info("Deleted {$deleted} existing plan add-ons for {$effectiveDate->format('Y-m-d')}");
		}

		// Mark all previous records as not current (only if not same date)
		PlanAddOn::where('is_current', true)
			->where('effective_date', '!=', $effectiveDate)
			->update(['is_current' => false]);

		// Headers are in row 0: soc, soc_description, rc_rate, gp_soc_desc, gp_soc
		// Data starts at row 1
		
		for ($rowIndex = 1; $rowIndex < count($rows); $rowIndex++) {
			$row = $rows[$rowIndex];
			
			// Debug first few rows
			if ($rowIndex <= 10) {
				$this->info("Processing row {$rowIndex}: Col0 empty? " . (empty($row[0]) ? 'YES' : 'NO') . 
						   ", Col2 empty? " . (empty($row[2]) ? 'YES' : 'NO'));
			}
			
			// Skip empty rows
			if (empty($row[0]) || !isset($row[2])) {
				continue;
			}
			
			$socCode = trim($row[0]);
			$addOnName = trim($row[1]);
			$monthlyRate = $this->parseDecimal($row[2]);
			
			if ($rowIndex <= 10) {
				$this->info("  Parsed: SOC={$socCode}, Name={$addOnName}, Rate={$monthlyRate}");
			}
			
			// Only import if we have a valid rate (allow $0)
			if ($monthlyRate < 0) {
				if ($rowIndex <= 10) {
					$this->info("  Skipped: Rate is < 0");
				}
				continue;
			}
			
			$category = isset($row[3]) ? trim($row[3]) : null;
			$groupSoc = isset($row[4]) ? trim($row[4]) : null;
			
			try {
				PlanAddOn::create([
					'soc_code' => $socCode,
					'add_on_name' => $addOnName,
					'monthly_rate' => $monthlyRate,
					'category' => $category,
					'group_soc' => $groupSoc,
					'effective_date' => $effectiveDate,
					'is_current' => true,
				]);
				$count++;
				
				if ($rowIndex <= 10) {
					$this->info("  ✓ Imported successfully");
				}
			} catch (\Exception $e) {
				$this->warn("Row {$rowIndex}: Error importing add-on '{$addOnName}' - " . $e->getMessage());
				$skipped++;
			}
		}

		if ($skipped > 0) {
			$this->info("Skipped {$skipped} rows");
		}

		return $count;
	}

    private function parseDecimal($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.00;
        }
        
        // Remove dollar signs, commas, and other non-numeric characters except decimal point and minus
        $cleanValue = preg_replace('/[^0-9.\-]/', '', $value);
        
        // If empty after cleaning, return 0
        if ($cleanValue === '') {
            return 0.00;
        }
        
        return (float) $cleanValue;
    }

    private function extractTier($planName): ?string
    {
        $planNameLower = strtolower($planName);
        
        if (stripos($planNameLower, 'ultra') !== false) {
            return 'Ultra';
        } elseif (stripos($planNameLower, 'max') !== false) {
            return 'Max';
        } elseif (stripos($planNameLower, 'select') !== false) {
            return 'Select';
        } elseif (stripos($planNameLower, 'lite') !== false) {
            return 'Lite';
        }
        
        return null;
    }

    private function extractDataAmount($planName): ?string
    {
        // Extract data amounts like "100GB", "175GB US", "200GB US/MX", etc.
        if (preg_match('/(\d+)\s*GB/i', $planName, $matches)) {
            $amount = $matches[0];
            
            // Check for US or MX
            if (stripos($planName, 'US') !== false) {
                $amount .= ' US';
            }
            if (stripos($planName, 'MX') !== false) {
                $amount .= '/MX';
            }
            
            return $amount;
        }
        
        return null;
    }

    private function extractPromoPrice($promoDescription): ?float
    {
        if (empty($promoDescription)) {
            return null;
        }
        
        // Look for patterns like "$65", "$65.00", etc.
        if (preg_match('/\$(\d+(?:\.\d{2})?)/', $promoDescription, $matches)) {
            return (float) $matches[1];
        }
        
        return null;
    }
}