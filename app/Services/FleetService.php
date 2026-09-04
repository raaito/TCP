<?php

namespace App\Services;

use App\Models\Corridor;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\Contracts\FleetServiceInterface;
use Illuminate\Support\Collection;

final class FleetService implements FleetServiceInterface
{
    public function createDriver(string $orgId, string $name, string $phoneNumber): Driver
    {
        return Driver::create([
            'org_id' => $orgId,
            'name' => $name,
            'phone_number' => $phoneNumber,
        ]);
    }

    public function createVehicle(string $orgId, string $plateNumber, ?string $capacityType = null): Vehicle
    {
        return Vehicle::create([
            'org_id' => $orgId,
            'plate_number' => strtoupper($plateNumber),
            'capacity_type' => $capacityType,
        ]);
    }

    public function createCorridor(string $name, string $origin, string $destination, array $waypoints = []): Corridor
    {
        return Corridor::create([
            'name' => $name,
            'origin' => $origin,
            'destination' => $destination,
            'waypoints' => $waypoints,
        ]);
    }

    public function getOrgDrivers(string $orgId): Collection
    {
        return Driver::where('org_id', $orgId)->orderBy('name')->get();
    }

    public function getOrgVehicles(string $orgId): Collection
    {
        return Vehicle::where('org_id', $orgId)->orderBy('plate_number')->get();
    }

    public function getAllCorridors(): Collection
    {
        return Corridor::orderBy('name')->get();
    }
}