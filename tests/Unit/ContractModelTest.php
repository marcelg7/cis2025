<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\Subscriber;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use App\Models\BellDevice;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use App\Models\ContractAddOn;
use App\Models\ContractOneTimeFee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractModelTest extends TestCase
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
    public function it_can_create_a_contract()
    {
        $contract = Contract::factory()->create();

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
        ]);
    }

    /** @test */
    public function it_belongs_to_a_subscriber()
    {
        $contract = Contract::factory()->create();

        $this->assertInstanceOf(Subscriber::class, $contract->subscriber);
        $this->assertEquals($contract->subscriber_id, $contract->subscriber->id);
    }

    /** @test */
    public function it_belongs_to_an_activity_type()
    {
        $contract = Contract::factory()->create();

        $this->assertInstanceOf(ActivityType::class, $contract->activityType);
    }

    /** @test */
    public function it_belongs_to_a_commitment_period()
    {
        $contract = Contract::factory()->create();

        $this->assertInstanceOf(CommitmentPeriod::class, $contract->commitmentPeriod);
    }

    /** @test */
    public function it_can_have_add_ons()
    {
        $contract = Contract::factory()->create();

        ContractAddOn::factory()->count(3)->create([
            'contract_id' => $contract->id,
        ]);

        $this->assertCount(3, $contract->addOns);
    }

    /** @test */
    public function it_can_have_one_time_fees()
    {
        $contract = Contract::factory()->create();

        ContractOneTimeFee::factory()->count(2)->create([
            'contract_id' => $contract->id,
        ]);

        $this->assertCount(2, $contract->oneTimeFees);
    }

    /** @test */
    public function it_calculates_total_cellular_rate_correctly()
    {
        $contract = Contract::factory()->create([
            'rate_plan_price' => 50.00,
            'mobile_internet_price' => 25.00,
        ]);

        $this->assertEquals(75.00, $contract->total_cellular_rate);
    }

    /** @test */
    public function it_calculates_total_cellular_rate_with_only_rate_plan()
    {
        $contract = Contract::factory()->create([
            'rate_plan_price' => 50.00,
            'mobile_internet_price' => null,
        ]);

        $this->assertEquals(50.00, $contract->total_cellular_rate);
    }

    /** @test */
    public function it_calculates_total_cellular_rate_with_only_mobile_internet()
    {
        $contract = Contract::factory()->create([
            'rate_plan_price' => null,
            'mobile_internet_price' => 25.00,
        ]);

        $this->assertEquals(25.00, $contract->total_cellular_rate);
    }

    /** @test */
    public function it_determines_if_financing_is_required()
    {
        $contract = Contract::factory()->create([
            'bell_device_id' => 1,
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => 100.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
        ]);

        $this->assertTrue($contract->requiresFinancing());
    }

    /** @test */
    public function it_determines_financing_not_required_when_no_device()
    {
        $contract = Contract::factory()->create([
            'bell_device_id' => null,
        ]);

        $this->assertFalse($contract->requiresFinancing());
    }

    /** @test */
    public function it_determines_financing_not_required_when_fully_paid()
    {
        $contract = Contract::factory()->create([
            'bell_device_id' => 1,
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => 500.00,
            'required_upfront_payment' => 300.00,
            'optional_down_payment' => 200.00,
        ]);

        $this->assertFalse($contract->requiresFinancing());
    }

    /** @test */
    public function it_calculates_total_financed_amount_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 50.00,
        ]);

        // 1200 - 200 - 100 - 50 = 850
        $this->assertEquals(850.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_calculates_total_financed_amount_with_zero_credits()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1000.00,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
        ]);

        $this->assertEquals(1000.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_calculates_total_financed_amount_never_negative()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 500.00,
            'agreement_credit_amount' => 300.00,
            'required_upfront_payment' => 200.00,
            'optional_down_payment' => 100.00,
        ]);

        // Would be -100, but should return 0
        $this->assertEquals(0.00, $contract->getTotalFinancedAmount());
    }

    /** @test */
    public function it_calculates_monthly_device_payment_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
            'required_upfront_payment' => 100.00,
            'optional_down_payment' => 100.00,
            'deferred_payment_amount' => 200.00,
        ]);

        // Financed: 1200 - 200 - 100 - 100 = 800
        // Remaining: 800 - 200 = 600
        // Monthly: 600 / 24 = 25
        $this->assertEquals(25.00, $contract->getMonthlyDevicePayment());
    }

    /** @test */
    public function it_calculates_monthly_device_payment_without_deferred()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 0.00,
            'required_upfront_payment' => 0.00,
            'optional_down_payment' => 0.00,
            'deferred_payment_amount' => 0.00,
        ]);

        // Financed: 1200
        // Monthly: 1200 / 24 = 50
        $this->assertEquals(50.00, $contract->getMonthlyDevicePayment());
    }

    /** @test */
    public function it_determines_if_dro_is_required()
    {
        $contract = Contract::factory()->create([
            'bell_dro_amount' => 150.00,
        ]);

        $this->assertTrue($contract->requiresDro());
    }

    /** @test */
    public function it_determines_dro_not_required_when_zero()
    {
        $contract = Contract::factory()->create([
            'bell_dro_amount' => 0.00,
        ]);

        $this->assertFalse($contract->requiresDro());
    }

    /** @test */
    public function it_determines_dro_not_required_when_null()
    {
        $contract = Contract::factory()->create([
            'bell_dro_amount' => null,
        ]);

        $this->assertFalse($contract->requiresDro());
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $contract = Contract::factory()->create([
            'start_date' => '2024-01-15',
            'end_date' => '2026-01-15',
            'contract_date' => '2024-01-10',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $contract->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $contract->end_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $contract->contract_date);
    }

    /** @test */
    public function it_casts_decimal_fields_correctly()
    {
        $contract = Contract::factory()->create([
            'bell_retail_price' => 1234.56,
            'rate_plan_price' => 89.99,
        ]);

        $this->assertEquals('1234.56', $contract->bell_retail_price);
        $this->assertEquals('89.99', $contract->rate_plan_price);
    }
}
