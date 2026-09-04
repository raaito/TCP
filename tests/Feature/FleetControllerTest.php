<?php

namespace Tests\Feature;

use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Models\DispatcherUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetControllerTest extends TestCase
{
    use RefreshDatabase;

    private DispatcherUser $user;
    private ClientOrg $org;

    protected function setUp(): void
    {
        parent::setUp();

        $corridor = Corridor::create([
            'name' => 'Lagos–Accra',
            'origin' => 'Lagos',
            'destination' => 'Accra',
        ]);

        $this->org = ClientOrg::create([
            'name' => 'Test Coop',
            'type' => 'cooperative',
            'corridor_id' => $corridor->id,
        ]);

        $this->user = DispatcherUser::create([
            'org_id' => $this->org->id,
            'name' => 'James Dispatcher',
            'phone_number' => '+2348010000001',
            'role' => 'dispatcher',
            'password' => 'password',
        ]);
    }

    public function test_manage_page_requires_auth(): void
    {
        $this->get(route('manage'))->assertRedirect(route('login'));
    }

    public function test_manage_page_loads(): void
    {
        $this->actingAs($this->user)->get(route('manage'))->assertOk();
    }

    public function test_add_driver(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.store'), [
                'name' => 'Musa Bello',
                'phone_number' => '+2348020000001',
            ]);

        $response->assertRedirect(route('manage'));

        $this->assertDatabaseHas('drivers', [
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);
    }

    public function test_add_vehicle(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post(route('vehicles.store'), [
                'plate_number' => 'lag-458-ak',
                'capacity_type' => '10-ton truck',
            ]);

        $response->assertRedirect(route('manage'));

        $this->assertDatabaseHas('vehicles', [
            'org_id' => $this->org->id,
            'plate_number' => 'LAG-458-AK',
        ]);
    }

    public function test_add_corridor(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post(route('corridors.store'), [
                'name' => 'Lagos–Cotonou',
                'origin' => 'Lagos',
                'destination' => 'Cotonou',
                'waypoints' => ['Seme Border'],
            ]);

        $response->assertRedirect(route('manage'));

        $this->assertDatabaseHas('corridors', [
            'name' => 'Lagos–Cotonou',
        ]);
    }

    public function test_duplicate_driver_phone_rejected(): void
    {
        $this->actingAs($this->user)->post(route('drivers.store'), [
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('drivers.store'), [
                'name' => 'Another Driver',
                'phone_number' => '+2348020000001',
            ]);

        $response->assertSessionHasErrors('phone_number');
        $this->assertDatabaseCount('drivers', 1);
    }

    public function test_duplicate_corridor_name_rejected(): void
    {
        $this->actingAs($this->user)->post(route('corridors.store'), [
            'name' => 'Lagos–Cotonou',
            'origin' => 'Lagos',
            'destination' => 'Cotonou',
        ]);

        $response = $this
            ->actingAs($this->user)
            ->post(route('corridors.store'), [
                'name' => 'Lagos–Cotonou',
                'origin' => 'Lagos',
                'destination' => 'Cotonou',
            ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('corridors', 2);
    }
}