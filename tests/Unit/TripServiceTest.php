<?php

namespace Tests\Unit;

use App\DTOs\CreateTripData;
use App\DTOs\LogCheckpointData;
use App\DTOs\UpdateTripData;
use App\Enums\CheckpointSource;
use App\Enums\DelayReason;
use App\Enums\TripStatus;
use App\Exceptions\Domain\DriverHasActiveTripException;
use App\Exceptions\Domain\TripNotFoundException;
use App\Models\CheckpointEvent;
use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Models\DispatcherUser;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use App\Services\Contracts\TripServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class TripServiceTest extends TestCase
{
    use RefreshDatabase;
    private TripServiceInterface $service;
    private ClientOrg $org;
    private Corridor $corridor;
    private Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(TripServiceInterface::class);

        $this->corridor = Corridor::create([
            'name' => 'Lagos–Accra',
            'origin' => 'Lagos, Nigeria',
            'destination' => 'Accra, Ghana',
            'waypoints' => ['Cotonou', 'Lomé'],
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

    public function test_create_trip_persists_correctly(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);

        $data = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: $this->vehicle->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'rice (50kg bags)',
            departureTime: now()->subHours(2)->toDateTimeString(),
            expectedArrival: now()->addHours(10)->toDateTimeString(),
            originLat: 6.5244,
            originLng: 3.3792,
            destinationLat: 5.5500,
            destinationLng: -0.2174,
            geofenceRadiusM: 200,
        );

        $trip = $this->service->createTrip($data);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'cargo_type' => 'rice (50kg bags)',
            'status' => TripStatus::Created->value,
            'auto_closed' => false,
            'geofence_radius_m' => 200,
        ]);
        $this->assertEqualsWithDelta(6.5244, $trip->origin_lat, 0.0001);
        $this->assertEqualsWithDelta(3.3792, $trip->origin_lng, 0.0001);
        $this->assertEqualsWithDelta(5.5500, $trip->destination_lat, 0.0001);
        $this->assertEqualsWithDelta(-0.2174, $trip->destination_lng, 0.0001);
    }

    public function test_create_trip_defaults_geofence_radius(): void
    {
        $data = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: $this->vehicle->id,
            driverId: null,
            corridorId: $this->corridor->id,
            cargoType: 'cement',
        );

        $trip = $this->service->createTrip($data);

        $this->assertEquals(200, $trip->geofence_radius_m);
        $this->assertSame(TripStatus::Created, $trip->status);
        $this->assertNull($trip->created_by);
    }

    public function test_create_trip_stamps_creator(): void
    {
        $dispatcher = DispatcherUser::create([
            'org_id' => $this->org->id,
            'name' => 'James Dispatcher',
            'phone_number' => '+2348010000001',
            'role' => 'dispatcher',
            'password' => 'password',
        ]);

        $data = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: $this->vehicle->id,
            driverId: null,
            corridorId: $this->corridor->id,
            cargoType: 'rice',
            createdBy: $dispatcher->id,
        );

        $trip = $this->service->createTrip($data);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'created_by' => $dispatcher->id,
        ]);
    }

    public function test_one_active_trip_per_driver(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Kwame Asante',
            'phone_number' => '+233500000001',
        ]);

        $data = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: $this->vehicle->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'rice',
        );

        $this->service->createTrip($data);

        $this->expectException(DriverHasActiveTripException::class);
        $this->expectExceptionMessage('Driver already has an active trip.');

        $data2 = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: Vehicle::create([
                'org_id' => $this->org->id,
                'plate_number' => 'V2-888-V2',
                'capacity_type' => '5-ton truck',
            ])->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'beans',
        );

        $this->service->createTrip($data2);
    }

    public function test_inactive_trips_do_not_block_same_driver(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Amina Yusuf',
            'phone_number' => '+254711000001',
        ]);

        $data1 = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: $this->vehicle->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'wheat',
        );

        $trip1 = $this->service->createTrip($data1);

        $trip1->update(['status' => TripStatus::Arrived]);

        $data2 = new CreateTripData(
            orgId: $this->org->id,
            vehicleId: Vehicle::create([
                'org_id' => $this->org->id,
                'plate_number' => 'V3-999-V3',
                'capacity_type' => '5-ton truck',
            ])->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'maize',
        );

        $trip2 = $this->service->createTrip($data2);

        $this->assertNotEquals($trip1->id, $trip2->id);
    }

    public function test_log_checkpoint_creates_event(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $event = $this->service->logCheckpoint(
            $trip->id,
            new LogCheckpointData(
                checkpointName: 'Cotonou border',
                source: CheckpointSource::Dispatcher,
                delayFlag: true,
                delayReason: DelayReason::Customs,
            ),
        );

        $this->assertDatabaseHas('checkpoint_events', [
            'id' => $event->id,
            'trip_id' => $trip->id,
            'checkpoint_name' => 'Cotonou border',
            'source' => CheckpointSource::Dispatcher->value,
            'delay_flag' => true,
            'delay_reason' => DelayReason::Customs->value,
        ]);
    }

    public function test_log_checkpoint_without_delay(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $event = $this->service->logCheckpoint(
            $trip->id,
            new LogCheckpointData(
                checkpointName: 'Departed Lagos',
                source: CheckpointSource::Dispatcher,
                delayFlag: false,
            ),
        );

        $this->assertFalse($event->delay_flag);
    }

    public function test_ussd_Arrived_closes_trip(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $this->service->logCheckpoint(
            $trip->id,
            new LogCheckpointData(
                checkpointName: 'Arrived',
                source: CheckpointSource::UssdRelay,
            ),
        );

        $trip->refresh();

        $this->assertSame(TripStatus::Arrived, $trip->status);
        $this->assertFalse($trip->auto_closed);
    }

    public function testCloseTripManually(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $closed = $this->service->closeTripManually($trip->id, 'Dispatchers');

        $this->assertSame(TripStatus::Arrived, $closed->status);
        $this->assertFalse($closed->auto_closed);

        $this->assertDatabaseHas('checkpoint_events', [
            'trip_id' => $trip->id,
            'checkpoint_name' => 'Closed manually: Dispatchers',
            'source' => CheckpointSource::Dispatcher->value,
        ]);
    }

    public function test_update_trip_updates_fields(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);

        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'corridor_id' => $this->corridor->id,
            'cargo_type' => 'rice',
            'status' => TripStatus::InTransit,
            'geofence_radius_m' => 200,
        ]);

        $data = new UpdateTripData(
            vehicleId: $this->vehicle->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'textiles',
            departureTime: now()->subHour()->toDateTimeString(),
            expectedArrival: now()->addHours(8)->toDateTimeString(),
            originLat: 6.5,
            originLng: 3.3,
            destinationLat: 5.6,
            destinationLng: -0.2,
            geofenceRadiusM: 350,
        );

        $updated = $this->service->updateTrip($trip->id, $this->org->id, $data);

        $this->assertSame($trip->id, $updated->id);
        $this->assertSame('textiles', $updated->cargo_type);
        $this->assertEqualsWithDelta(6.5, $updated->origin_lat, 0.0001);
        $this->assertEqualsWithDelta(-0.2, $updated->destination_lng, 0.0001);
        $this->assertEquals(350, $updated->geofence_radius_m);
        $this->assertSame(TripStatus::InTransit, $updated->status);
    }

    public function test_update_trip_keeps_own_driver(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);

        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'corridor_id' => $this->corridor->id,
            'cargo_type' => 'rice',
            'status' => TripStatus::InTransit,
        ]);

        $updated = $this->service->updateTrip($trip->id, $this->org->id, new UpdateTripData(
            vehicleId: $this->vehicle->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
            cargoType: 'beans',
        ));

        $this->assertSame($driver->id, $updated->driver_id);
    }

    public function test_update_trip_rejects_driver_with_other_active_trip(): void
    {
        $driver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);

        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::Created,
        ]);

        Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $driver->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $this->expectException(DriverHasActiveTripException::class);

        $this->service->updateTrip($trip->id, $this->org->id, new UpdateTripData(
            vehicleId: $this->vehicle->id,
            driverId: $driver->id,
            corridorId: $this->corridor->id,
        ));
    }

    public function test_update_trip_scopes_to_org(): void
    {
        $org2 = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $trip = Trip::create([
            'org_id' => $org2->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $this->expectException(TripNotFoundException::class);

        $this->service->updateTrip($trip->id, $this->org->id, new UpdateTripData(
            vehicleId: $this->vehicle->id,
            driverId: null,
            corridorId: $this->corridor->id,
        ));
    }

    public function test_get_drivers_for_trip_edit_includes_current_driver(): void
    {
        $busyDriver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);
        $freeDriver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Kwame Asante',
            'phone_number' => '+233500000001',
        ]);

        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $busyDriver->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $drivers = $this->service->getDriversForTripEdit($trip->id, $this->org->id);

        $this->assertCount(2, $drivers);
        $this->assertTrue($drivers->pluck('id')->contains($busyDriver->id));
        $this->assertTrue($drivers->pluck('id')->contains($freeDriver->id));
    }

    public function test_get_available_drivers_excludes_busy_drivers(): void
    {
        $busyDriver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);
        $freeDriver = Driver::create([
            'org_id' => $this->org->id,
            'name' => 'Kwame Asante',
            'phone_number' => '+233500000001',
        ]);

        Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'driver_id' => $busyDriver->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $available = $this->service->getAvailableDrivers($this->org->id);

        $this->assertCount(1, $available);
        $this->assertSame($freeDriver->id, $available->first()->id);
    }

    public function test_get_active_trips_scopes_to_org(): void
    {
        $org2 = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $v1 = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        Trip::create([
            'org_id' => $org2->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $active = $this->service->getActiveTrips($this->org->id);

        $this->assertCount(1, $active);
        $this->assertSame($v1->id, $active->first()->id);
    }

    public function test_update_trip_status(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $updated = $this->service->updateTripStatus($trip->id, TripStatus::Delayed);

        $this->assertSame(TripStatus::Delayed, $updated->status);
    }

    public function test_cannot_set_trip_status_back_to_created(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot set trip status back to created.');

        $this->service->updateTripStatus($trip->id, TripStatus::Created);
    }

    public function test_update_to_cancelled(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $updated = $this->service->updateTripStatus($trip->id, TripStatus::Cancelled);

        $this->assertSame(TripStatus::Cancelled, $updated->status);
    }

    public function test_get_trip_for_org_returns_trip_for_own_org(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $found = $this->service->getTripForOrg($trip->id, $this->org->id);

        $this->assertSame($trip->id, $found->id);
    }

    public function test_get_trip_for_org_throws_for_other_org(): void
    {
        $org2 = ClientOrg::create([
            'name' => 'Other Coop',
            'type' => 'cooperative',
            'corridor_id' => $this->corridor->id,
        ]);

        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $this->expectException(TripNotFoundException::class);

        $this->service->getTripForOrg($trip->id, $org2->id);
    }

    public function test_get_trip_for_org_throws_for_missing_trip(): void
    {
        $this->expectException(TripNotFoundException::class);

        $this->service->getTripForOrg('does-not-exist', $this->org->id);
    }

    public function test_get_trip_detail_for_org_loads_relations_ordered(): void
    {
        $trip = Trip::create([
            'org_id' => $this->org->id,
            'vehicle_id' => $this->vehicle->id,
            'corridor_id' => $this->corridor->id,
            'status' => TripStatus::InTransit,
        ]);

        $trip->checkpointEvents()->create([
            'checkpoint_name' => 'Cotonou border',
            'source' => CheckpointSource::WhatsApp,
            'reported_at' => now()->subHour(),
            'delay_flag' => false,
        ]);
        $trip->checkpointEvents()->create([
            'checkpoint_name' => 'Departed Lagos',
            'source' => CheckpointSource::Dispatcher,
            'reported_at' => now()->subHours(3),
            'delay_flag' => false,
        ]);

        $detail = $this->service->getTripDetailForOrg($trip->id, $this->org->id);

        $this->assertTrue($detail->relationLoaded('checkpointEvents'));
        $this->assertTrue($detail->relationLoaded('locationPings'));
        $this->assertTrue($detail->relationLoaded('vehicle'));
        $this->assertTrue($detail->relationLoaded('corridor'));

        $names = $detail->checkpointEvents->pluck('checkpoint_name')->all();
        $this->assertSame(['Departed Lagos', 'Cotonou border'], $names);
    }
}