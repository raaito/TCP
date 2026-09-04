<?php

namespace Tests\Feature;

use App\Enums\TripStatus;
use App\Models\CheckpointEvent;
use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Models\DispatcherUser;
use App\Models\Driver;
use App\Models\LocationPing;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripControllerTest extends TestCase
{
    use RefreshDatabase;

    private DispatcherUser $user;
    private ClientOrg $org;
    private Corridor $corridor;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->corridor = Corridor::create([
            'name' => 'Lagos–Accra',
            'origin' => 'Lagos',
            'destination' => 'Accra',
        ]);

        $this->org = ClientOrg::create([
            'name' => 'Test Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $this->vehicle = Vehicle::create([
            'org_id' => $this->org->id,
            'plate_number' => 'TST-001-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $this->user = DispatcherUser::create([
            'org_id' => $this->org->id,
            'name' => 'James Dispatcher',
            'phone_number' => '+2348010000001',
            'role' => 'dispatcher',
            'password' => 'password',
        ]);
    }

    public function test_store_trip_requires_auth(): void
    {
        $response = $this->postJson(route('trips.store'), [
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_store_trip_creates_and_redirects(): void
    {
        $response = $this
            ->actingAs($this->user)
            ->post(route('trips.store'), [
                'vehicle_id' => $this->vehicle->id,
                'corridor_id' => $this->corridor->id,
                'cargo_type' => 'rice',
            ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('trips', [
            'org_id' => $this->org->id,
            'cargo_type' => 'rice',
        ]);
    }

    public function test_store_trip_rejects_driver_with_active_trip(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);

        Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.store'), [
                'vehicle_id' => $this->vehicle->id,
                'driver_id' => $driver->id,
                'corridor_id' => $this->corridor->id,
                'cargo_type' => 'textiles',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('driver_id');
    }

    public function test_active_trips_returns_json(): void
    {
        Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::Arrived,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->getJson(route('trips.active'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
    }

    public function test_log_checkpoint_creates_event(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.checkpoint', $trip->id), [
                'checkpoint_name' => 'Cotonou border',
                'source' => 'dispatcher',
                'delay_flag' => true,
                'delay_reason' => 'customs',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.checkpoint_name', 'Cotonou border');
    }

    public function test_close_trip_manually_sets_arrived(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.close', $trip->id), [
                'reason' => 'Driver confirmed by phone',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', TripStatus::Arrived->value);
    }

    public function test_update_status_changes_trip_status(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.status', $trip->id), [
                'status' => 'delayed',
            ]);

        $response->assertOk();
        $response->assertJsonPath('data.status', TripStatus::Delayed->value);
    }

    public function test_show_trip_loads_page(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('trips.show', $trip->id));

        $response->assertOk();
    }

    public function test_show_trip_renders_detail_page_with_checkpoints(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        CheckpointEvent::create([
            'trip_id' => $trip->id,
            'checkpoint_name' => 'Departed Lagos',
            'source' => 'dispatcher',
            'delay_flag' => false,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('trips.show', $trip->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('TripDetail')
            ->has('trip')
            ->where('trip.id', $trip->id)
            ->has('trip.checkpoint_events', 1));
    }

    public function test_cannot_show_other_orgs_trip(): void
    {
        $otherOrg = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $otherVehicle = Vehicle::create([
            'org_id' => $otherOrg->id,
            'plate_number' => 'OTH-001-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $trip = Trip::create([
            'org_id' => $otherOrg->id,
            'vehicle_id' => $otherVehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('trips.show', $trip->id));

        $response->assertStatus(404);
    }

    public function test_cannot_close_other_orgs_trip(): void
    {
        $otherOrg = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $otherVehicle = Vehicle::create([
            'org_id' => $otherOrg->id,
            'plate_number' => 'OTH-002-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $trip = Trip::create([
            'org_id' => $otherOrg->id,
            'vehicle_id' => $otherVehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.close', $trip->id));

        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'trip_not_found');
    }

    public function test_cannot_update_status_of_other_orgs_trip(): void
    {
        $otherOrg = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $otherVehicle = Vehicle::create([
            'org_id' => $otherOrg->id,
            'plate_number' => 'OTH-003-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $trip = Trip::create([
            'org_id' => $otherOrg->id,
            'vehicle_id' => $otherVehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.status', $trip->id), ['status' => 'delayed']);

        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'trip_not_found');
    }

    public function test_cannot_log_checkpoint_for_other_orgs_trip(): void
    {
        $otherOrg = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $otherVehicle = Vehicle::create([
            'org_id' => $otherOrg->id,
            'plate_number' => 'OTH-004-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $trip = Trip::create([
            'org_id' => $otherOrg->id,
            'vehicle_id' => $otherVehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->postJson(route('trips.checkpoint', $trip->id), [
                'checkpoint_name' => 'Cotonou border',
                'source' => 'dispatcher',
            ]);

        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'trip_not_found');
    }

    public function test_edit_trip_page_loads(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'cargo_type' => 'rice',
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('trips.edit', $trip->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('TripEdit')
            ->where('trip.id', $trip->id)
            ->has('vehicles')
            ->has('drivers')
            ->has('corridors'));
    }

    public function test_update_trip_updates_and_redirects(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'cargo_type' => 'rice',
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->put(route('trips.update', $trip->id), [
                'vehicle_id' => $this->vehicle->id,
                'driver_id' => '',
                'corridor_id' => $this->corridor->id,
                'cargo_type' => 'textiles',
            ]);

        $response->assertRedirect(route('trips.show', $trip->id));

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'cargo_type' => 'textiles',
        ]);
    }

    public function test_update_trip_requires_auth(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this->put(route('trips.update', $trip->id), [
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_cannot_edit_other_orgs_trip(): void
    {
        $otherOrg = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $otherVehicle = Vehicle::create([
            'org_id' => $otherOrg->id,
            'plate_number' => 'OTH-005-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $trip = Trip::create([
            'org_id' => $otherOrg->id,
            'vehicle_id' => $otherVehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->get(route('trips.edit', $trip->id));

        $response->assertStatus(404);
    }

    public function test_cannot_update_other_orgs_trip(): void
    {
        $otherOrg = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $otherVehicle = Vehicle::create([
            'org_id' => $otherOrg->id,
            'plate_number' => 'OTH-006-TC',
            'capacity_type' => '10-ton truck',
        ]);

        $trip = Trip::create([
            'org_id' => $otherOrg->id,
            'vehicle_id' => $otherVehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $response = $this
            ->actingAs($this->user)
            ->putJson(route('trips.update', $trip->id), [
                'vehicle_id' => $otherVehicle->id,
                'driver_id' => '',
                'corridor_id' => $this->corridor->id,
            ]);

        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'trip_not_found');
    }
}