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

        $effectiveDate = $this->option('date') 
            ? Carbon::parse($this->option('date')) 
            : Carbon::today();
        
        $replace = $this->option('replace');

        $this->info("Importing Cellular Price Plans...");
        $this->info("Effective Date: {$effectiveDate->format('Y-m-d')}");
        
        try {
            $spreadsheet = IOFactory::load($filePath);
            
            $this->info("\n📱 Importing Rate Plan Overview...");
            $ratePlanCount = $this->importRatePlans($spreadsheet, $effectiveDate, $replace);
            $this->info("✓ Imported {$ratePlanCount} rate plans");
            
            $this->info("\n📡 Importing Mobile Internet Plans...");
            $mobileInternetCount = $this->importMobileInternet($spreadsheet, $effectiveDate, $replace);
            $this->info("✓ Imported {$mobileInternetCount} mobile internet plans");
            
            $this->info("\n➕ Importing Plan Add-Ons...");
            $addOnCount = $this->importPlanAddOns($spreadsheet, $effectiveDate, $replace);
            $this->info("✓ Imported {$addOnCount} plan add-ons");
            
            $this->info("\n✅ Import completed successfully!");
            
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

        RatePlan::where('is_current', true)
            ->where('effective_date', '!=', $effectiveDate)
            ->update(['is_current' => false]);

        // Start from row 8
        for ($rowIndex = 8; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[$rowIndex];
            
            // BYOD plans (columns B-F: indices 1-5)
            if (!empty($row[1]) && !empty($row[2]) && isset($row[3])) {
                $socCode = trim($row[1]);
                $planName = trim($row[2]);
                $basePrice = $this->parseDecimal($row[3]);
                
                if ($basePrice > 0) {
					// Column F (index 5) contains credit info
					$creditInfo = $this->extractCreditInfo($row[5] ?? '');

					// Column E (index 4) - other promos (NOT credits)
					$promoDescription = isset($row[4]) && !empty(trim($row[4])) ? trim($row[4]) : null;
					$promoPrice = $this->extractPromoPrice($promoDescription);

					$tier = $this->extractTier($planName);
					$isInternational = stripos($planName, 'INT') !== false;
					$isUsMexico = stripos($planName, 'US') !== false || stripos($planName, 'MX') !== false;
					$dataAmount = $this->extractDataAmount($planName);

					try {
						// Find the most recent plan with this SOC code to preserve custom features
						$existingPlan = RatePlan::where('soc_code', $socCode)
							->orderBy('effective_date', 'desc')
							->first();

						$planData = [
							'plan_name' => $planName,
							'plan_type' => 'byod',
							'tier' => $tier,
							'base_price' => $basePrice,
							'promo_price' => $promoPrice,
							'promo_description' => $promoDescription,
							'credit_eligible' => $creditInfo['eligible'],
							'credit_amount' => $creditInfo['amount'],
							'credit_type' => $creditInfo['type'],
							'data_amount' => $dataAmount,
							'is_international' => $isInternational,
							'is_us_mexico' => $isUsMexico,
							'effective_date' => $effectiveDate,
							'is_current' => true,
							'is_active' => empty($row[0]) || $row[0] !== 'X',
						];
						
						if ($existingPlan && !empty($existingPlan->features)) {
							$planData['features'] = $existingPlan->features;
						}
						
						RatePlan::updateOrCreate(
							[
								'soc_code' => $socCode,
								'effective_date' => $effectiveDate
							],
							$planData
						);
						
						$count++;
					} catch (\Exception $e) {
						$this->warn("Error importing BYOD plan '{$planName}': " . $e->getMessage());
					}
				}
            }
            
            // SmartPay plans (columns H-M: indices 7-12)
            if (!empty($row[7]) && !empty($row[8]) && isset($row[9])) {
                $socCode = trim($row[7]);
                $planName = trim($row[8]);
                $basePrice = $this->parseDecimal($row[9]);
                
                if ($basePrice > 0) {
					// Column M (index 12) contains credit info for SmartPay
					$creditInfo = $this->extractCreditInfo($row[12] ?? '');

					// Column K (index 10) is tier, Column L (index 11) is other promos
					$tier = isset($row[10]) && !empty(trim($row[10])) ? trim($row[10]) : null;
					$promoDescription = isset($row[11]) && !empty(trim($row[11])) ? trim($row[11]) : null;
					$promoPrice = $this->extractPromoPrice($promoDescription);

					$isInternational = stripos($planName, 'INT') !== false;
					$isUsMexico = stripos($planName, 'US') !== false || stripos($planName, 'MX') !== false;
					$dataAmount = $this->extractDataAmount($planName);

					try {
						// Find the most recent plan with this SOC code to preserve custom features
						$existingPlan = RatePlan::where('soc_code', $socCode)
							->orderBy('effective_date', 'desc')
							->first();

						$planData = [
							'plan_name' => $planName,
							'plan_type' => 'smartpay',
							'tier' => $tier,
							'base_price' => $basePrice,
							'promo_price' => $promoPrice,
							'promo_description' => $promoDescription,
							'credit_eligible' => $creditInfo['eligible'],
							'credit_amount' => $creditInfo['amount'],
							'credit_type' => $creditInfo['type'],
							'data_amount' => $dataAmount,
							'is_international' => $isInternational,
							'is_us_mexico' => $isUsMexico,
							'effective_date' => $effectiveDate,
							'is_current' => true,
							'is_active' => empty($row[6]) || $row[6] !== 'X',
						];
						
						if ($existingPlan && !empty($existingPlan->features)) {
							$planData['features'] = $existingPlan->features;
						}
						
						RatePlan::updateOrCreate(
							[
								'soc_code' => $socCode,
								'effective_date' => $effectiveDate
							],
							$planData
						);
						
						$count++;
					} catch (\Exception $e) {
						$this->warn("Error importing SmartPay plan '{$planName}': " . $e->getMessage());
					}
				}
            }
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
                // Find the most recent plan with this SOC code to preserve custom description
                $existingPlan = MobileInternetPlan::where('soc_code', $socCode)
                    ->orderBy('effective_date', 'desc')
                    ->first();

                $planData = [
                    'plan_name' => $planName,
                    'monthly_rate' => $monthlyRate,
                    'category' => $category,
                    'promo_group' => $promoGroup,
                    'effective_date' => $effectiveDate,
                    'is_current' => true,
                ];
                
                if ($existingPlan && !empty($existingPlan->description)) {
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
                $this->warn("Error importing mobile internet plan '{$planName}': " . $e->getMessage());
            }
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

        if ($replace) {
            $deleted = PlanAddOn::where('effective_date', $effectiveDate)->delete();
            $this->info("Deleted {$deleted} existing plan add-ons");
        }

        PlanAddOn::where('is_current', true)
            ->where('effective_date', '!=', $effectiveDate)
            ->update(['is_current' => false]);

        for ($rowIndex = 1; $rowIndex < count($rows); $rowIndex++) {
            $row = $rows[$rowIndex];
            
            if (empty($row[0]) || !isset($row[2])) {
                continue;
            }
            
            $socCode = trim($row[0]);
            $addOnName = trim($row[1]);
            $monthlyRate = $this->parseDecimal($row[2]);
            
            if ($monthlyRate < 0) {
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
            } catch (\Exception $e) {
                $this->warn("Error importing add-on '{$addOnName}': " . $e->getMessage());
            }
        }

        return $count;
    }

    private function parseDecimal($value): float
    {
        if (is_null($value) || $value === '') {
            return 0.00;
        }
        
        $cleanValue = preg_replace('/[^0-9.\-]/', '', $value);
        
        if ($cleanValue === '') {
            return 0.00;
        }
        
        return (float) $cleanValue;
    }

    private function extractTier($planName): ?string
    {
        $planNameLower = strtolower($planName);

        if (stripos($planNameLower, 'ultra') !== false) return 'Ultra';
        if (stripos($planNameLower, 'max') !== false) return 'Max';
        if (stripos($planNameLower, 'select') !== false) return 'Select';
        if (stripos($planNameLower, 'lite') !== false) return 'Lite';
        if (stripos($planNameLower, 'basic') !== false) return 'Basic';

        return null;
    }

    private function extractDataAmount($planName): ?string
    {
        if (preg_match('/(\d+)\s*GB/i', $planName, $matches)) {
            $amount = $matches[0];
            
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
        
        if (preg_match('/\$(\d+(?:\.\d{2})?)/', $promoDescription, $matches)) {
            return (float) $matches[1];
        }
        
        return null;
    }
	
	
	/**
	 * Extract credit information from a cell value
	 * Supports formats like:
	 * - "$10 Hay Credit"
	 * - "$60 ($10 Hay Credit)"
	 * - "$15 Store Credit"
	 * - "$20 Loyalty Bonus"
	 * 
	 * @param string $cellValue
	 * @return array ['eligible' => bool, 'amount' => float|null, 'type' => string|null]
	 */
	private function extractCreditInfo($cellValue): array
	{
		if (empty($cellValue)) {
			return ['eligible' => false, 'amount' => null, 'type' => null];
		}
		
		$cellValue = trim($cellValue);
		
		// Pattern 1: Look for "$XX Credit" or "$XX.XX Credit" anywhere in the string
		if (preg_match('/\$(\d+(?:\.\d{2})?)\s+([A-Za-z\s]+Credit)/i', $cellValue, $matches)) {
			return [
				'eligible' => true,
				'amount' => (float) $matches[1],
				'type' => trim($matches[2])
			];
		}
		
		// Pattern 2: Look for "($XX Credit)" in parentheses
		if (preg_match('/\(\$(\d+(?:\.\d{2})?)\s+([A-Za-z\s]+Credit)\)/i', $cellValue, $matches)) {
			return [
				'eligible' => true,
				'amount' => (float) $matches[1],
				'type' => trim($matches[2])
			];
		}
		
		// Pattern 3: Look for "$XX Bonus" or other keywords
		if (preg_match('/\$(\d+(?:\.\d{2})?)\s+([A-Za-z\s]+(?:Bonus|Discount|Rebate))/i', $cellValue, $matches)) {
			return [
				'eligible' => true,
				'amount' => (float) $matches[1],
				'type' => trim($matches[2])
			];
		}
		
		return ['eligible' => false, 'amount' => null, 'type' => null];
	}	
}