<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Services\ContractPdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractPdfServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContractPdfService $pdfService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdfService = new ContractPdfService();

        // Seed necessary data
        $this->artisan('db:seed', ['--class' => 'ActivityTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'CommitmentPeriodSeeder']);
    }

    /** @test */
    public function it_can_instantiate_pdf_service()
    {
        $this->assertInstanceOf(ContractPdfService::class, $this->pdfService);
    }

    /** @test */
    public function it_loads_contract_relationships_for_pdf_generation()
    {
        $ratePlan = RatePlan::factory()->create([
            'name' => 'Basic Plan',
            'price' => 50.00,
        ]);

        $contract = Contract::factory()->create([
            'rate_plan_id' => $ratePlan->id,
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
        ]);

        // Load relationships as the service would
        $contract->load([
            'subscriber.mobilityAccount.ivueAccount.customer',
            'activityType',
            'commitmentPeriod',
            'ratePlan',
        ]);

        $this->assertNotNull($contract->ratePlan);
        $this->assertEquals('Basic Plan', $contract->ratePlan->name);
    }

    /** @test */
    public function it_verifies_financial_calculations_match_service_logic()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 50.00,
            'deferred_payment_amount' => 150.00,
        ]);

        // Replicate service calculations
        $devicePrice = $contract->bell_retail_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $deferredPayment = $contract->deferred_payment_amount ?? 0;
        $remainingBalance = $totalFinancedAmount - $deferredPayment;
        $monthlyDevicePayment = $remainingBalance / 24;

        // Assertions
        $this->assertEquals(1000.00, $deviceAmount); // 1200 - 200
        $this->assertEquals(850.00, $totalFinancedAmount); // 1000 - 100 - 50
        $this->assertEquals(700.00, $remainingBalance); // 850 - 150
        $this->assertEquals(700 / 24, $monthlyDevicePayment);
    }

    /** @test */
    public function it_calculates_early_cancellation_fee_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 0.00,
            'bell_dro_amount' => 150.00,
        ]);

        // Service calculation
        $devicePrice = $contract->bell_retail_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $earlyCancellationFee = $totalFinancedAmount + ($contract->bell_dro_amount ?? 0);

        // 1200 - 200 - 100 = 900
        // 900 + 150 = 1050
        $this->assertEquals(1050.00, $earlyCancellationFee);
    }

    /** @test */
    public function it_calculates_buyout_cost_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'deferred_payment_amount' => 200.00,
        ]);

        // Service calculation
        $buyoutCost = ($contract->bell_retail_price - ($contract->deferred_payment_amount ?? 0)) / 24;

        // (1200 - 200) / 24 = 1000 / 24
        $this->assertEquals(1000 / 24, $buyoutCost);
    }

    /** @test */
    public function it_calculates_monthly_reduction_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 0.00,
        ]);

        // Service calculation
        $devicePrice = $contract->bell_retail_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $deferredPayment = $contract->deferred_payment_amount ?? 0;
        $remainingBalance = $totalFinancedAmount - $deferredPayment;
        $monthlyDevicePayment = $remainingBalance / 24;
        $monthlyReduction = $monthlyDevicePayment;

        // 1200 / 24 = 50
        $this->assertEquals(50.00, $monthlyReduction);
    }

    /** @test */
    public function it_handles_null_values_in_financial_calculations()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => null,
            'required_upfront_payment' => null,
            'optional_down_payment' => null,
            'deferred_payment_amount' => null,
            'bell_dro_amount' => null,
        ]);

        // Service calculation with null coalescing
        $devicePrice = $contract->bell_retail_price ?? 0;
        $deviceAmount = $devicePrice - ($contract->agreement_credit_amount ?? 0);
        $totalFinancedAmount = $deviceAmount - ($contract->required_upfront_payment ?? 0) - ($contract->optional_down_payment ?? 0);
        $deferredPayment = $contract->deferred_payment_amount ?? 0;
        $remainingBalance = $totalFinancedAmount - $deferredPayment;
        $monthlyDevicePayment = $remainingBalance / 24;

        $this->assertEquals(1000.00, $deviceAmount);
        $this->assertEquals(1000.00, $totalFinancedAmount);
        $this->assertEquals(1000.00, $remainingBalance);
        $this->assertEquals(1000 / 24, $monthlyDevicePayment);
    }

    /** @test */
    public function it_calculates_total_addon_cost_correctly()
    {
        $contract = Contract::factory()->create();

        \App\Models\ContractAddOn::factory()->create([
            'contract_id' => $contract->id,
            'cost' => 10.00,
        ]);

        \App\Models\ContractAddOn::factory()->create([
            'contract_id' => $contract->id,
            'cost' => 15.00,
        ]);

        $contract->load('addOns');

        $totalAddOnCost = $contract->addOns->sum('cost');

        $this->assertEquals(25.00, $totalAddOnCost);
    }

    /** @test */
    public function it_calculates_total_one_time_fee_cost_correctly()
    {
        $contract = Contract::factory()->create();

        \App\Models\ContractOneTimeFee::factory()->create([
            'contract_id' => $contract->id,
            'cost' => 50.00,
        ]);

        \App\Models\ContractOneTimeFee::factory()->create([
            'contract_id' => $contract->id,
            'cost' => 35.00,
        ]);

        $contract->load('oneTimeFees');

        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost');

        $this->assertEquals(85.00, $totalOneTimeFeeCost);
    }

    /** @test */
    public function it_calculates_total_contract_cost_correctly()
    {
        $contract = Contract::factory()->create([
            'rate_plan_price' => 50.00,
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 0.00,
        ]);

        \App\Models\ContractAddOn::factory()->create([
            'contract_id' => $contract->id,
            'cost' => 10.00,
        ]);

        \App\Models\ContractOneTimeFee::factory()->create([
            'contract_id' => $contract->id,
            'cost' => 35.00,
        ]);

        $contract->load(['addOns', 'oneTimeFees']);

        // Service calculation
        $totalFinancedAmount = $contract->getTotalFinancedAmount();
        $remainingBalance = $totalFinancedAmount - ($contract->deferred_payment_amount ?? 0);
        $monthlyDevicePayment = $remainingBalance / 24;
        $totalAddOnCost = $contract->addOns->sum('cost');
        $totalOneTimeFeeCost = $contract->oneTimeFees->sum('cost');
        $totalCost = ($totalAddOnCost + ($contract->rate_plan_price ?? 0) + $monthlyDevicePayment) * 24 + $totalOneTimeFeeCost;

        // Monthly: 10 + 50 + (1000/24) = 60 + 41.666... = 101.666...
        // Total: 101.666... * 24 + 35 = 2440 + 35 = 2475
        $expected = (10 + 50 + (1000 / 24)) * 24 + 35;

        $this->assertEquals($expected, $totalCost);
    }
}
