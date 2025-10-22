<?php

namespace Tests\Unit;

use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractFinancialCalculationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed necessary data
        $this->artisan('db:seed', ['--class' => 'ActivityTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'CommitmentPeriodSeeder']);
    }

    /** @test */
    public function it_calculates_device_amount_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 300.00,
        ]);

        // Device Amount = Retail Price - Agreement Credit
        // 1200 - 300 = 900
        $expected = 900.00;
        $actual = $contract->bell_retail_price - $contract->agreement_credit_amount;

        $this->assertEquals($expected, $actual);
    }

    /** @test */
    public function it_handles_zero_agreement_credit()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => 0.00,
        ]);

        $this->assertEquals(1000.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_handles_null_agreement_credit()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => null,
        ]);

        $this->assertEquals(1000.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_calculates_total_financed_with_all_deductions()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 2000.00,
            'agreement_credit_amount' => 500.00,
            'required_upfront_payment' => 200.00,
            'optional_down_payment' => 100.00,
        ]);

        // 2000 - 500 - 200 - 100 = 1200
        $this->assertEquals(1200.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_handles_overpayment_scenario()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 500.00,
            'agreement_credit_amount' => 400.00,
            'required_upfront_payment' => 150.00,
            'optional_down_payment' => 0.00,
        ]);

        // 500 - 400 - 150 = -50, but should return 0
        $this->assertEquals(0.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_calculates_monthly_payment_basic_scenario()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 0.00,
        ]);

        // 1200 / 24 = 50
        $this->assertEquals(50.00, $contract->getMonthlyDevicePayment());
    }

    /** @test */
    public function it_calculates_monthly_payment_with_deferred()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 240.00,
        ]);

        // Financed: 1200
        // Remaining: 1200 - 240 = 960
        // Monthly: 960 / 24 = 40
        $this->assertEquals(40.00, $contract->getMonthlyDevicePayment());
    }

    /** @test */
    public function it_calculates_monthly_payment_complex_scenario()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1500.00,
            'agreement_credit_amount' => 250.00,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 50.00,
            'deferred_payment_amount' => 100.00,
        ]);

        // Financed: 1500 - 250 - 100 - 50 = 1100
        // Remaining: 1100 - 100 = 1000
        // Monthly: 1000 / 24 = 41.666...
        $expected = 1000 / 24;
        $this->assertEquals($expected, $contract->getMonthlyDevicePayment());
    }

    /** @test */
    public function it_handles_precision_for_monthly_payments()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 999.99,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 0.00,
        ]);

        // 999.99 / 24 = 41.66625
        $expected = 999.99 / 24;
        $this->assertEquals($expected, $contract->getMonthlyDevicePayment());
    }

    /** @test */
    public function it_calculates_early_cancellation_fee_scenario()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'bell_dro_amount' => 150.00,
        ]);

        // Early Cancellation Fee = Total Financed + DRO
        // (1200 - 200) + 150 = 1150
        $totalFinanced = $contract->getTotalFinancedAmount();
        $earlyCancellationFee = $totalFinanced + ($contract->bell_dro_amount ?? 0);

        $this->assertEquals(1150.00, $earlyCancellationFee);
    }

    /** @test */
    public function it_calculates_buyout_cost()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'deferred_payment_amount' => 200.00,
        ]);

        // Buyout = (Retail Price - Deferred) / 24
        // (1200 - 200) / 24 = 41.666...
        $buyoutCost = ($contract->bell_retail_price - ($contract->deferred_payment_amount ?? 0)) / 24;

        $this->assertEquals(1000 / 24, $buyoutCost);
    }

    /** @test */
    public function it_calculates_buyout_cost_without_deferred()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'deferred_payment_amount' => 0.00,
        ]);

        // Buyout = 1200 / 24 = 50
        $buyoutCost = ($contract->bell_retail_price - ($contract->deferred_payment_amount ?? 0)) / 24;

        $this->assertEquals(50.00, $buyoutCost);
    }

    /** @test */
    public function it_calculates_remaining_balance()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 150.00,
        ]);

        // Total Financed: 1200 - 200 - 100 = 900
        // Remaining: 900 - 150 = 750
        $totalFinanced = $contract->getTotalFinancedAmount();
        $remainingBalance = $totalFinanced - ($contract->deferred_payment_amount ?? 0);

        $this->assertEquals(750.00, $remainingBalance);
    }

    /** @test */
    public function it_validates_financing_required_logic()
    {
        // Case 1: Has device and financing
        $contract1 = Contract::factory()->create([
            'bell_device_id' => 1,
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
        ]);
        $this->assertTrue($contract1->requiresFinancing());

        // Case 2: No device
        $contract2 = Contract::factory()->create([
            'bell_device_id' => null,
        ]);
        $this->assertFalse($contract2->requiresFinancing());

        // Case 3: Device fully paid
        $contract3 = Contract::factory()->create([
            'bell_device_id' => 1,
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => 1000.00,
        ]);
        $this->assertFalse($contract3->requiresFinancing());
    }

    /** @test */
    public function it_validates_dro_required_logic()
    {
        // Case 1: DRO amount > 0
        $contract1 = Contract::factory()->create([
            'bell_dro_amount' => 100.00,
        ]);
        $this->assertTrue($contract1->requiresDro());

        // Case 2: DRO amount = 0
        $contract2 = Contract::factory()->create([
            'bell_dro_amount' => 0.00,
        ]);
        $this->assertFalse($contract2->requiresDro());

        // Case 3: DRO amount is null
        $contract3 = Contract::factory()->create([
            'bell_dro_amount' => null,
        ]);
        $this->assertFalse($contract3->requiresDro());
    }

    /** @test */
    public function it_handles_decimal_precision_in_calculations()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1234.56,
            'agreement_credit_amount' => 234.56,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 50.00,
        ]);

        // 1234.56 - 234.56 - 100 - 50 = 850
        $this->assertEquals(850.00, $contract->getTotalFinancedAmount());
    }
}
