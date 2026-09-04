<?php

namespace App\Services\Contracts;

use App\Models\Corridor;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

interface FleetServiceInterface
{
    public function createDriver(string $orgId, string $name, string $phoneNumber): Driver;

    public function createVehicle(string $orgId, string $plateNumber, ?string $capacityType = null): Vehicle;

    public function createCorridor(string $name, string $origin, string $destination, array $waypoints = []): Corridor;

    public function getOrgDrivers(string $orgId): Collection;

    public function getOrgVehicles(string $orgId): Collection;

    public function getAllCorridors(): Collection;
}