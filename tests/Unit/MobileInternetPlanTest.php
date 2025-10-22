<?php

namespace Tests\Unit;

use App\Models\MobileInternetPlan;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileInternetPlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_mobile_internet_plan()
    {
        $plan = MobileInternetPlan::factory()->create([
            'plan_name' => 'Mobile Internet 10GB',
            'monthly_rate' => 25.00,
        ]);

        $this->assertDatabaseHas('mobile_internet_plans', [
            'plan_name' => 'Mobile Internet 10GB',
            'monthly_rate' => 25.00,
        ]);
    }

    /** @test */
    public function it_formats_display_name_correctly()
    {
        $plan = MobileInternetPlan::factory()->create([
            'plan_name' => 'Mobile Internet 5GB',
            'monthly_rate' => 20.00,
        ]);

        $expected = 'Mobile Internet 5GB - $20.00/mo';
        $this->assertEquals($expected, $plan->display_name);
    }

    /** @test */
    public function it_scopes_current_plans()
    {
        MobileInternetPlan::factory()->create(['is_current' => true]);
        MobileInternetPlan::factory()->create(['is_current' => false]);

        $currentPlans = MobileInternetPlan::current()->get();

        $this->assertCount(1, $currentPlans);
    }

    /** @test */
    public function it_scopes_active_plans()
    {
        MobileInternetPlan::factory()->create(['is_active' => true]);
        MobileInternetPlan::factory()->create(['is_active' => false]);

        $activePlans = MobileInternetPlan::active()->get();

        $this->assertCount(1, $activePlans);
    }

    /** @test */
    public function it_scopes_by_category()
    {
        MobileInternetPlan::factory()->create(['category' => 'mobile']);
        MobileInternetPlan::factory()->create(['category' => 'tablet']);

        $mobilePlans = MobileInternetPlan::ofCategory('mobile')->get();

        $this->assertCount(1, $mobilePlans);
        $this->assertEquals('mobile', $mobilePlans->first()->category);
    }

    /** @test */
    public function it_has_many_contracts()
    {
        $this->artisan('db:seed', ['--class' => 'ActivityTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'CommitmentPeriodSeeder']);

        $plan = MobileInternetPlan::factory()->create();

        Contract::factory()->count(2)->create([
            'mobile_internet_plan_id' => $plan->id,
        ]);

        $this->assertCount(2, $plan->contracts);
    }

    /** @test */
    public function it_gets_pricing_by_soc_code()
    {
        MobileInternetPlan::factory()->create([
            'soc_code' => 'MI5GB',
            'is_current' => true,
            'is_active' => true,
        ]);

        $pricing = MobileInternetPlan::getPricing('MI5GB');

        $this->assertNotNull($pricing);
        $this->assertEquals('MI5GB', $pricing->soc_code);
    }

    /** @test */
    public function it_returns_null_for_non_existent_soc_code()
    {
        $pricing = MobileInternetPlan::getPricing('NONEXISTENT');

        $this->assertNull($pricing);
    }

    /** @test */
    public function it_gets_current_plans()
    {
        MobileInternetPlan::factory()->create([
            'is_current' => true,
            'is_active' => true,
            'monthly_rate' => 20.00,
        ]);

        MobileInternetPlan::factory()->create([
            'is_current' => true,
            'is_active' => true,
            'monthly_rate' => 30.00,
        ]);

        MobileInternetPlan::factory()->create([
            'is_current' => false,
            'is_active' => true,
        ]);

        $currentPlans = MobileInternetPlan::getCurrentPlans();

        $this->assertCount(2, $currentPlans);
        // Should be ordered by monthly_rate
        $this->assertEquals(20.00, $currentPlans->first()->monthly_rate);
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $plan = MobileInternetPlan::factory()->create([
            'is_current' => true,
            'is_active' => false,
        ]);

        $this->assertTrue($plan->is_current);
        $this->assertFalse($plan->is_active);
    }

    /** @test */
    public function it_casts_decimal_fields_correctly()
    {
        $plan = MobileInternetPlan::factory()->create([
            'monthly_rate' => 25.99,
        ]);

        $this->assertEquals('25.99', $plan->monthly_rate);
    }

    /** @test */
    public function it_casts_date_fields_correctly()
    {
        $plan = MobileInternetPlan::factory()->create([
            'effective_date' => '2024-01-15',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $plan->effective_date);
    }
}
