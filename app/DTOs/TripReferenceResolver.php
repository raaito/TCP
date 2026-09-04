<?php

namespace App\DTOs;

use App\Models\Corridor;
use App\Models\Driver;
use App\Models\Vehicle;

trait TripReferenceResolver
{
    private static function resolveVehicleId(string $value, string $orgId): string
    {
        $query = Vehicle::query();

        if (self::isUuid($value)) {
            $query->where('id', $value);
        } else {
            $normalized = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $value));
            $query->whereRaw("UPPER(regexp_replace(plate_number, '[^a-zA-Z0-9]', '', 'g')) = ?", [$normalized]);
        }

        $vehicle = $query->first();

        if ($vehicle) {
            return $vehicle->id;
        }

        return Vehicle::create([
            'org_id' => $orgId,
            'plate_number' => strtoupper($value),
        ])->id;
    }

    private static function resolveDriverId(?string $value, string $orgId): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $query = Driver::query();

        if (self::isUuid($value)) {
            $query->where('id', $value);
        } else {
            $query->where('phone_number', $value)
                ->orWhereRaw('LOWER(name) = LOWER(?)', [$value]);
        }

        $driver = $query->first();

        if ($driver) {
            return $driver->id;
        }

        return Driver::create([
            'org_id' => $orgId,
            'name' => $value,
            'phone_number' => $value,
        ])->id;
    }

    private static function resolveCorridorId(string $value): string
    {
        $query = Corridor::query();

        if (self::isUuid($value)) {
            $query->where('id', $value);
        } else {
            $query->whereRaw('LOWER(name) = LOWER(?)', [$value]);
        }

        $corridor = $query->first();

        if ($corridor) {
            return $corridor->id;
        }

        return Corridor::create([
            'name' => $value,
            'origin' => $value,
            'destination' => $value,
        ])->id;
    }

    private static function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value);
    }
}
