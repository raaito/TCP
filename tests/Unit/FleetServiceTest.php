<?php

namespace Tests\Unit;

use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Services\Contracts\FleetServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetServiceTest extends TestCase
{
    use RefreshDatabase;

    private FleetServiceInterface $service;
    private ClientOrg $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(FleetServiceInterface::class);

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
    }

    public function test_create_driver(): void
    {
        $driver = $this->service->createDriver($this->org->id, 'Musa Bello', '+2348020000001');

        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'org_id' => $this->org->id,
            'name' => 'Musa Bello',
            'phone_number' => '+2348020000001',
        ]);
    }

    public function test_create_vehicle_uppercases_plate(): void
    {
        $vehicle = $this->service->createVehicle($this->org->id, 'lag-458-ak', '10-ton truck');

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'plate_number' => 'LAG-458-AK',
            'capacity_type' => '10-ton truck',
        ]);
    }

    public function test_create_corridor_with_waypoints(): void
    {
        $corridor = $this->service->createCorridor('Lagos–Cotonou', 'Lagos', 'Cotonou', ['Seme Border']);

        $this->assertDatabaseHas('corridors', [
            'id' => $corridor->id,
            'name' => 'Lagos–Cotonou',
            'origin' => 'Lagos',
            'destination' => 'Cotonou',
        ]);

        $this->assertSame(['Seme Border'], $corridor->waypoints);
    }

    public function test_lists_are_org_scoped_for_drivers_and_vehicles(): void
    {
        $this->service->createDriver($this->org->id, 'Musa Bello', '+2348020000001');
        $this->service->createVehicle($this->org->id, 'LAG-458-AK');

        $this->assertCount(1, $this->service->getOrgDrivers($this->org->id));
        $this->assertCount(1, $this->service->getOrgVehicles($this->org->id));
    }
}