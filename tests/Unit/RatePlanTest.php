<?php

namespace Tests\Unit;

use App\Models\RatePlan;
use App\Models\Contract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RatePlanTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_rate_plan()
    {
        $ratePlan = RatePlan::factory()->create([
            'plan_name' => 'Premium Plan',
            'base_price' => 75.00,
        ]);

        $this->assertDatabaseHas('rate_plans', [
            'plan_name' => 'Premium Plan',
            'base_price' => 75.00,
        ]);
    }

    /** @test */
    public function it_returns_effective_price_when_no_promo()
    {
        $ratePlan = RatePlan::factory()->create([
            'base_price' => 75.00,
            'promo_price' => null,
        ]);

        $this->assertEquals(75.00, $ratePlan->effective_price);
    }

    /** @test */
    public function it_returns_promo_price_as_effective_price()
    {
        $ratePlan = RatePlan::factory()->create([
            'base_price' => 75.00,
            'promo_price' => 60.00,
        ]);

        $this->assertEquals(60.00, $ratePlan->effective_price);
    }

    /** @test */
    public function it_detects_when_plan_has_promo()
    {
        $ratePlan = RatePlan::factory()->create([
            'base_price' => 75.00,
            'promo_price' => 60.00,
        ]);

        $this->assertTrue($ratePlan->has_promo);
    }

    /** @test */
    public function it_detects_when_plan_has_no_promo()
    {
        $ratePlan = RatePlan::factory()->create([
            'base_price' => 75.00,
            'promo_price' => null,
        ]);

        $this->assertFalse($ratePlan->has_promo);
    }

    /** @test */
    public function it_formats_display_name_without_promo()
    {
        $ratePlan = RatePlan::factory()->create([
            'plan_name' => 'Basic Plan',
            'base_price' => 50.00,
            'promo_price' => null,
        ]);

        $expected = 'Basic Plan - $50.00';
        $this->assertEquals($expected, $ratePlan->display_name);
    }

    /** @test */
    public function it_formats_display_name_with_promo()
    {
        $ratePlan = RatePlan::factory()->create([
            'plan_name' => 'Premium Plan',
            'base_price' => 75.00,
            'promo_price' => 60.00,
        ]);

        $expected = 'Premium Plan - $60.00 (was $75.00)';
        $this->assertEquals($expected, $ratePlan->display_name);
    }

    /** @test */
    public function it_scopes_current_plans()
    {
        RatePlan::factory()->create(['is_current' => true]);
        RatePlan::factory()->create(['is_current' => false]);

        $currentPlans = RatePlan::current()->get();

        $this->assertCount(1, $currentPlans);
    }

    /** @test */
    public function it_scopes_active_plans()
    {
        RatePlan::factory()->create(['is_active' => true]);
        RatePlan::factory()->create(['is_active' => false]);

        $activePlans = RatePlan::active()->get();

        $this->assertCount(1, $activePlans);
    }

    /** @test */
    public function it_scopes_by_plan_type()
    {
        RatePlan::factory()->create(['plan_type' => 'byod']);
        RatePlan::factory()->create(['plan_type' => 'smartpay']);

        $byodPlans = RatePlan::ofType('byod')->get();

        $this->assertCount(1, $byodPlans);
        $this->assertEquals('byod', $byodPlans->first()->plan_type);
    }

    /** @test */
    public function it_scopes_by_tier()
    {
        RatePlan::factory()->create(['tier' => 'Ultra']);
        RatePlan::factory()->create(['tier' => 'Lite']);

        $ultraPlans = RatePlan::ofTier('Ultra')->get();

        $this->assertCount(1, $ultraPlans);
        $this->assertEquals('Ultra', $ultraPlans->first()->tier);
    }

    /** @test */
    public function it_has_many_contracts()
    {
        $this->artisan('db:seed', ['--class' => 'ActivityTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'CommitmentPeriodSeeder']);

        $ratePlan = RatePlan::factory()->create();

        Contract::factory()->count(3)->create([
            'rate_plan_id' => $ratePlan->id,
        ]);

        $this->assertCount(3, $ratePlan->contracts);
    }

    /** @test */
    public function it_gets_pricing_by_soc_code()
    {
        RatePlan::factory()->create([
            'soc_code' => 'ABC123',
            'is_current' => true,
            'is_active' => true,
        ]);

        $pricing = RatePlan::getPricing('ABC123');

        $this->assertNotNull($pricing);
        $this->assertEquals('ABC123', $pricing->soc_code);
    }

    /** @test */
    public function it_gets_pricing_by_soc_code_and_tier()
    {
        RatePlan::factory()->create([
            'soc_code' => 'ABC123',
            'tier' => 'Ultra',
            'is_current' => true,
            'is_active' => true,
        ]);

        RatePlan::factory()->create([
            'soc_code' => 'ABC123',
            'tier' => 'Lite',
            'is_current' => true,
            'is_active' => true,
        ]);

        $pricing = RatePlan::getPricing('ABC123', 'Ultra');

        $this->assertNotNull($pricing);
        $this->assertEquals('Ultra', $pricing->tier);
    }

    /** @test */
    public function it_returns_null_for_non_existent_soc_code()
    {
        $pricing = RatePlan::getPricing('NONEXISTENT');

        $this->assertNull($pricing);
    }

    /** @test */
    public function it_gets_current_byod_plans()
    {
        RatePlan::factory()->create([
            'plan_type' => 'byod',
            'is_current' => true,
            'is_active' => true,
        ]);

        RatePlan::factory()->create([
            'plan_type' => 'smartpay',
            'is_current' => true,
            'is_active' => true,
        ]);

        $byodPlans = RatePlan::getCurrentByodPlans();

        $this->assertCount(1, $byodPlans);
        $this->assertEquals('byod', $byodPlans->first()->plan_type);
    }

    /** @test */
    public function it_gets_current_smartpay_plans()
    {
        RatePlan::factory()->create([
            'plan_type' => 'byod',
            'is_current' => true,
            'is_active' => true,
        ]);

        RatePlan::factory()->create([
            'plan_type' => 'smartpay',
            'is_current' => true,
            'is_active' => true,
        ]);

        $smartpayPlans = RatePlan::getCurrentSmartPayPlans();

        $this->assertCount(1, $smartpayPlans);
        $this->assertEquals('smartpay', $smartpayPlans->first()->plan_type);
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $ratePlan = RatePlan::factory()->create([
            'is_current' => true,
            'is_active' => true,
            'is_international' => false,
        ]);

        $this->assertTrue($ratePlan->is_current);
        $this->assertTrue($ratePlan->is_active);
        $this->assertFalse($ratePlan->is_international);
    }

    /** @test */
    public function it_casts_decimal_fields_correctly()
    {
        $ratePlan = RatePlan::factory()->create([
            'base_price' => 75.99,
            'promo_price' => 59.99,
        ]);

        $this->assertEquals('75.99', $ratePlan->base_price);
        $this->assertEquals('59.99', $ratePlan->promo_price);
    }
}
