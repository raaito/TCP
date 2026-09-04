<?php

namespace App\DTOs;

use App\Http\Requests\UpdateTripRequest;

final class UpdateTripData
{
    use TripReferenceResolver;

    public function __construct(
        public readonly string $vehicleId,
        public readonly ?string $driverId,
        public readonly string $corridorId,
        public readonly ?string $cargoType = null,
        public readonly ?string $departureTime = null,
        public readonly ?string $expectedArrival = null,
        public readonly ?float $destinationLat = null,
        public readonly ?float $destinationLng = null,
        public readonly ?float $originLat = null,
        public readonly ?float $originLng = null,
        public readonly ?int $geofenceRadiusM = null,
    ) {}

    public static function fromRequest(UpdateTripRequest $request): self
    {
        $orgId = $request->user()->org_id;

        return new self(
            vehicleId: self::resolveVehicleId($request->validated('vehicle_id'), $orgId),
            driverId: self::resolveDriverId($request->validated('driver_id'), $orgId),
            corridorId: self::resolveCorridorId($request->validated('corridor_id')),
            cargoType: $request->validated('cargo_type'),
            departureTime: $request->validated('departure_time'),
            expectedArrival: $request->validated('expected_arrival'),
            destinationLat: $request->validated('destination_lat'),
            destinationLng: $request->validated('destination_lng'),
            originLat: $request->validated('origin_lat'),
            originLng: $request->validated('origin_lng'),
            geofenceRadiusM: $request->validated('geofence_radius_m'),
        );
    }
}
