<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Contract;
use App\Models\Subscriber;
use App\Models\ActivityType;
use App\Models\CommitmentPeriod;
use App\Models\RatePlan;
use App\Models\MobileInternetPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user
        $this->user = User::factory()->create();

        // Seed necessary data
        $this->artisan('db:seed', ['--class' => 'ActivityTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'CommitmentPeriodSeeder']);
    }

    /** @test */
    public function it_displays_contracts_index_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('contracts.index'));

        $response->assertStatus(200);
        $response->assertViewIs('contracts.index');
    }

    /** @test */
    public function it_lists_contracts_on_index_page()
    {
        $this->actingAs($this->user);

        Contract::factory()->count(3)->create();

        $response = $this->get(route('contracts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('contracts');
    }

    /** @test */
    public function it_paginates_contracts()
    {
        $this->actingAs($this->user);

        Contract::factory()->count(15)->create();

        $response = $this->get(route('contracts.index'));

        $response->assertStatus(200);
        $response->assertViewHas('contracts', function ($contracts) {
            return $contracts->total() === 15 && $contracts->perPage() === 10;
        });
    }

    /** @test */
    public function it_filters_contracts_by_customer()
    {
        $this->actingAs($this->user);

        $subscriber = Subscriber::factory()->create();
        $subscriber->mobilityAccount->ivueAccount->customer->update([
            'display_name' => 'John Smith',
        ]);

        Contract::factory()->create(['subscriber_id' => $subscriber->id]);
        Contract::factory()->count(2)->create();

        $response = $this->get(route('contracts.index', ['customer' => 'John']));

        $response->assertStatus(200);
        $response->assertViewHas('contracts', function ($contracts) {
            return $contracts->total() === 1;
        });
    }

    /** @test */
    public function it_filters_contracts_by_device()
    {
        $this->actingAs($this->user);

        Contract::factory()->create(['manufacturer' => 'Apple', 'model' => 'iPhone 15']);
        Contract::factory()->create(['manufacturer' => 'Samsung', 'model' => 'Galaxy S24']);

        $response = $this->get(route('contracts.index', ['device' => 'iPhone']));

        $response->assertStatus(200);
        $response->assertViewHas('contracts', function ($contracts) {
            return $contracts->total() === 1;
        });
    }

    /** @test */
    public function it_filters_contracts_by_start_date()
    {
        $this->actingAs($this->user);

        Contract::factory()->create(['start_date' => '2024-01-15']);
        Contract::factory()->create(['start_date' => '2024-02-15']);

        $response = $this->get(route('contracts.index', ['start_date' => '2024-01-15']));

        $response->assertStatus(200);
        $response->assertViewHas('contracts', function ($contracts) {
            return $contracts->total() === 1;
        });
    }

    /** @test */
    public function it_displays_create_contract_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('contracts.create'));

        $response->assertStatus(200);
        $response->assertViewIs('contracts.create');
    }

    /** @test */
    public function it_displays_create_contract_page_with_subscriber()
    {
        $this->actingAs($this->user);

        $subscriber = Subscriber::factory()->create();

        $response = $this->get(route('contracts.create', ['subscriberId' => $subscriber->id]));

        $response->assertStatus(200);
        $response->assertViewHas('subscriber');
    }

    /** @test */
    public function it_shows_a_contract()
    {
        $this->actingAs($this->user);

        $contract = Contract::factory()->create();

        $response = $this->get(route('contracts.show', $contract));

        $response->assertStatus(200);
        $response->assertViewIs('contracts.show');
        $response->assertViewHas('contract');
    }

    /** @test */
    public function it_displays_edit_contract_page()
    {
        $this->actingAs($this->user);

        $contract = Contract::factory()->create();

        $response = $this->get(route('contracts.edit', $contract));

        $response->assertStatus(200);
        $response->assertViewIs('contracts.edit');
        $response->assertViewHas('contract');
    }

    /** @test */
    public function it_creates_a_new_contract()
    {
        $this->actingAs($this->user);

        $subscriber = Subscriber::factory()->create();
        $activityType = ActivityType::first();
        $commitmentPeriod = CommitmentPeriod::first();

        $contractData = [
            'subscriber_id' => $subscriber->id,
            'activity_type_id' => $activityType->id,
            'commitment_period_id' => $commitmentPeriod->id,
            'contract_date' => '2024-01-15',
            'start_date' => '2024-01-15',
            'end_date' => '2026-01-15',
            'location' => 'zurich',
            'status' => 'draft',
            'bell_retail_price' => 1200.00,
            'agreement_credit_amount' => 200.00,
        ];

        $response = $this->post(route('contracts.store'), $contractData);

        $this->assertDatabaseHas('contracts', [
            'subscriber_id' => $subscriber->id,
            'status' => 'draft',
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function it_updates_a_contract()
    {
        $this->actingAs($this->user);

        $contract = Contract::factory()->create([
            'bell_retail_price' => 1000.00,
        ]);

        $updateData = [
            'subscriber_id' => $contract->subscriber_id,
            'activity_type_id' => $contract->activity_type_id,
            'commitment_period_id' => $contract->commitment_period_id,
            'contract_date' => $contract->contract_date->format('Y-m-d'),
            'start_date' => $contract->start_date->format('Y-m-d'),
            'end_date' => $contract->end_date->format('Y-m-d'),
            'location' => $contract->location,
            'status' => $contract->status,
            'bell_retail_price' => 1500.00,
        ];

        $response = $this->put(route('contracts.update', $contract), $updateData);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'bell_retail_price' => 1500.00,
        ]);

        $response->assertRedirect();
    }

    /** @test */
    public function it_deletes_a_contract()
    {
        $this->actingAs($this->user);

        $contract = Contract::factory()->create();

        $response = $this->delete(route('contracts.destroy', $contract));

        $this->assertDatabaseMissing('contracts', [
            'id' => $contract->id,
        ]);

        $response->assertRedirect(route('contracts.index'));
    }

    /** @test */
    public function guests_cannot_access_contracts_index()
    {
        $response = $this->get(route('contracts.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_cannot_create_contracts()
    {
        $response = $this->get(route('contracts.create'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function guests_cannot_view_contracts()
    {
        $contract = Contract::factory()->create();

        $response = $this->get(route('contracts.show', $contract));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_eager_loads_relationships_on_index()
    {
        $this->actingAs($this->user);

        Contract::factory()->count(3)->create();

        $response = $this->get(route('contracts.index'));

        $response->assertStatus(200);

        // Verify relationships are loaded to avoid N+1 queries
        $response->assertViewHas('contracts', function ($contracts) {
            return $contracts->first()->relationLoaded('subscriber') &&
                   $contracts->first()->subscriber->relationLoaded('mobilityAccount');
        });
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('contracts.store'), []);

        $response->assertSessionHasErrors([
            'subscriber_id',
            'activity_type_id',
            'commitment_period_id',
        ]);
    }

    /** @test */
    public function it_displays_contract_with_add_ons()
    {
        $this->actingAs($this->user);

        $contract = Contract::factory()->create();

        \App\Models\ContractAddOn::factory()->count(2)->create([
            'contract_id' => $contract->id,
        ]);

        $response = $this->get(route('contracts.show', $contract));

        $response->assertStatus(200);
        $response->assertSee('Add-Ons', false);
    }

    /** @test */
    public function it_displays_contract_with_one_time_fees()
    {
        $this->actingAs($this->user);

        $contract = Contract::factory()->create();

        \App\Models\ContractOneTimeFee::factory()->create([
            'contract_id' => $contract->id,
            'name' => 'Connection Fee',
        ]);

        $response = $this->get(route('contracts.show', $contract));

        $response->assertStatus(200);
        $response->assertSee('Connection Fee');
    }
}
