<?php

namespace Tests\Unit;

use App\Enums\CheckpointSource;
use App\Enums\TripStatus;
use App\Models\CheckpointEvent;
use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Models\LocationPing;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Services\Contracts\GeofenceServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeofenceServiceTest extends TestCase
{
    use RefreshDatabase;

    private GeofenceServiceInterface $service;
    private ClientOrg $org;
    private Corridor $corridor;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(GeofenceServiceInterface::class);

        $this->corridor = Corridor::create([
            'name' => 'Lagos–Accra',
            'origin' => 'Lagos, Nigeria',
            'destination' => 'Accra, Ghana',
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
    }

    private function trip(array $attributes = []): Trip
    {
        return Trip::create(array_merge([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
            'destination_lat' => 5.5500,
            'destination_lng' => -0.2174,
            'geofence_radius_m' => 200,
        ], $attributes));
    }

    public function test_haversine_returns_zero_for_same_point(): void
    {
        $this->assertEqualsWithDelta(0.0, $this->service->haversineM(5.55, -0.2174, 5.55, -0.2174), 0.001);
    }

    public function test_haversine_roughly_1km_between_two_known_points(): void
    {
        $meters = $this->service->haversineM(5.55, -0.2174, 5.55, -0.1900);

        $this->assertGreaterThan(2500, $meters);
        $this->assertLessThan(3100, $meters);
    }

    public function test_ping_within_geofence_auto_closes_trip(): void
    {
        $trip = $this->trip();

        $ping = new LocationPing([
            'trip_id' => $trip->id,
            'lat' => 5.5501,
            'lng' => -0.2173,
            'recorded_at' => now(),
            'source' => 'driver_phone',
        ]);
        $ping->save();

        $result = $this->service->evaluatePing($ping);

        $this->assertSame(TripStatus::Arrived, $result->status);
        $this->assertTrue($result->auto_closed);
        $this->assertNotNull($result->last_ping_at);

        $this->assertDatabaseHas('checkpoint_events', [
            'trip_id' => $trip->id,
            'checkpoint_name' => 'Destination (auto-detected via geofence)',
            'source' => CheckpointSource::System->value,
        ]);
    }

    public function test_ping_outside_geofence_does_not_close_trip(): void
    {
        $trip = $this->trip();

        $ping = new LocationPing([
            'trip_id' => $trip->id,
            // ~2km south of the destination
            'lat' => 5.5300,
            'lng' => -0.2174,
            'recorded_at' => now(),
            'source' => 'driver_phone',
        ]);
        $ping->save();

        $result = $this->service->evaluatePing($ping);

        $this->assertSame(TripStatus::InTransit, $result->status);
        $this->assertFalse($result->auto_closed);
    }

    public function test_stale_trips_finds_silent_trip_and_reports_distance(): void
    {
        $this->trip([
            'last_ping_at' => now()->subHours(10),
        ]);

        $trip = Trip::first();
        LocationPing::create([
            'trip_id' => $trip->id,
            'lat' => 5.5300,
            'lng' => -0.2174,
            'recorded_at' => now()->subHours(10),
            'source' => 'driver_phone',
        ]);

        $stale = $this->service->staleTrips(6);

        $this->assertCount(1, $stale);
        $this->assertSame($trip->id, $stale->first()['trip_id']);
        $this->assertGreaterThan(6, $stale->first()['hours_since_last_ping']);
        $this->assertNotNull($stale->first()['last_known_distance_m']);
    }

    public function test_recently_pinged_trip_is_not_stale(): void
    {
        $this->trip([
            'last_ping_at' => now()->subMinutes(30),
        ]);

        $stale = $this->service->staleTrips(6);

        $this->assertTrue($stale->isEmpty());
    }

    public function test_mark_stale_trips_delayed_flips_status(): void
    {
        $trip = $this->trip([
            'last_ping_at' => now()->subHours(12),
        ]);

        $affected = $this->service->markStaleTripsDelayed(6);

        $this->assertContains($trip->id, $affected->all());

        $trip->refresh();
        $this->assertSame(TripStatus::Delayed, $trip->status);

        $this->assertDatabaseHas('checkpoint_events', [
            'trip_id' => $trip->id,
            'source' => CheckpointSource::System->value,
            'delay_flag' => true,
        ]);
    }

    public function test_mark_stale_skips_arrived_trips(): void
    {
        $trip = $this->trip([
            'status' => TripStatus::Arrived,
            'last_ping_at' => now()->subHours(12),
        ]);

        $affected = $this->service->markStaleTripsDelayed(6);

        $this->assertTrue($affected->isEmpty());
        $trip->refresh();
        $this->assertSame(TripStatus::Arrived, $trip->status);
    }

    public function test_mark_stale_does_not_touch_trip_without_location_ping(): void
    {
        $trip = $this->trip([
            'status' => TripStatus::InTransit,
            'last_ping_at' => null,
        ]);

        $affected = $this->service->markStaleTripsDelayed(6);

        $this->assertTrue($affected->isEmpty());
        $this->assertSame(TripStatus::InTransit, $trip->status);
    }
}