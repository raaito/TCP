<?php

namespace App\Services;

use App\DTOs\CreateTripData;
use App\DTOs\LogCheckpointData;
use App\DTOs\UpdateTripData;
use App\Enums\CheckpointSource;
use App\Enums\TripStatus;
use App\Events\TripArrived;
use App\Events\TripCreated;
use App\Models\CheckpointEvent;
use App\Models\Driver;
use App\Models\Trip;
use App\Exceptions\Domain\DriverHasActiveTripException;
use App\Exceptions\Domain\TripNotFoundException;
use App\Services\Contracts\TripServiceInterface;
use Illuminate\Support\Collection;
use RuntimeException;

final class TripService implements TripServiceInterface
{
    public function createTrip(CreateTripData $data): Trip
    {
        if ($data->driverId !== null) {
            $existing = Trip::where('driver_id', $data->driverId)
                ->whereIn('status', [TripStatus::Created, TripStatus::InTransit, TripStatus::Delayed])
                ->exists();

            if ($existing) {
                throw new DriverHasActiveTripException();
            }
        }

        $trip = Trip::create([
            'org_id'            => $data->orgId,
            'vehicle_id'        => $data->vehicleId,
            'driver_id'         => $data->driverId,
            'corridor_id'       => $data->corridorId,
            'cargo_type'        => $data->cargoType,
            'departure_time'    => $data->departureTime,
            'expected_arrival'  => $data->expectedArrival,
            'destination_lat'   => $data->destinationLat,
            'destination_lng'   => $data->destinationLng,
            'origin_lat'        => $data->originLat,
            'origin_lng'        => $data->originLng,
            'geofence_radius_m' => $data->geofenceRadiusM ?? 200,
            'status'            => TripStatus::Created,
            'auto_closed'       => false,
            'created_by'        => $data->createdBy,
        ]);

        event(new TripCreated($trip));

        return $trip;
    }

    public function updateTrip(string $tripId, string $orgId, UpdateTripData $data): Trip
    {
        $trip = $this->getTripForOrg($tripId, $orgId);

        if ($data->driverId !== null) {
            $conflict = Trip::where('driver_id', $data->driverId)
                ->where('id', '!=', $trip->id)
                ->whereIn('status', [TripStatus::Created, TripStatus::InTransit, TripStatus::Delayed])
                ->exists();

            if ($conflict) {
                throw new DriverHasActiveTripException();
            }
        }

        $trip->update([
            'vehicle_id'        => $data->vehicleId,
            'driver_id'         => $data->driverId,
            'corridor_id'       => $data->corridorId,
            'cargo_type'        => $data->cargoType,
            'departure_time'    => $data->departureTime,
            'expected_arrival'  => $data->expectedArrival,
            'destination_lat'   => $data->destinationLat,
            'destination_lng'   => $data->destinationLng,
            'origin_lat'        => $data->originLat,
            'origin_lng'        => $data->originLng,
            'geofence_radius_m' => $data->geofenceRadiusM ?? 200,
        ]);

        return $trip;
    }

    public function logCheckpoint(string $tripId, LogCheckpointData $data): CheckpointEvent
    {
        $trip = Trip::findOrFail($tripId);

        $event = $trip->checkpointEvents()->create([
            'checkpoint_name' => $data->checkpointName,
            'source'          => $data->source,
            'delay_flag'      => $data->delayFlag,
            'delay_reason'    => $data->delayReason,
        ]);

        if ($data->source === CheckpointSource::UssdRelay && $data->checkpointName === 'Arrived') {
            $trip->update(['status' => TripStatus::Arrived, 'auto_closed' => false]);
            event(new TripArrived($trip, 'ussd_relay'));
        }

        return $event;
    }

    public function closeTripManually(string $tripId, string $reason): Trip
    {
        $trip = Trip::findOrFail($tripId);

        $trip->update([
            'status' => TripStatus::Arrived,
            'auto_closed' => false,
        ]);

        $trip->checkpointEvents()->create([
            'checkpoint_name' => "Closed manually: {$reason}",
            'source' => CheckpointSource::Dispatcher,
            'delay_flag' => false,
        ]);

        event(new TripArrived($trip, "manual_close:{$reason}"));

        return $trip;
    }

    public function updateTripStatus(string $tripId, TripStatus $status): Trip
    {
        $trip = Trip::findOrFail($tripId);

        $statusEnum = is_string($status) ? TripStatus::from($status) : $status;

        if ($statusEnum === TripStatus::Created) {
            throw new RuntimeException('Cannot set trip status back to created.');
        }

        $trip->update(['status' => $statusEnum]);

        return $trip;
    }

    public function getActiveTrips(string $orgId): Collection
    {
        return Trip::where('org_id', $orgId)
            ->whereIn('status', [TripStatus::Created, TripStatus::InTransit, TripStatus::Delayed])
            ->with(['vehicle', 'driver', 'corridor'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAvailableDrivers(string $orgId): Collection
    {
        $busyDriverIds = Trip::where('org_id', $orgId)
            ->whereIn('status', [TripStatus::Created, TripStatus::InTransit, TripStatus::Delayed])
            ->whereNotNull('driver_id')
            ->pluck('driver_id');

        return Driver::where('org_id', $orgId)
            ->whereNotIn('id', $busyDriverIds)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getDriversForTripEdit(string $tripId, string $orgId): Collection
    {
        $trip = $this->getTripForOrg($tripId, $orgId);

        $ids = $this->getAvailableDrivers($orgId)->pluck('id')->all();

        if ($trip->driver_id !== null && !in_array($trip->driver_id, $ids, true)) {
            $ids[] = $trip->driver_id;
        }

        return Driver::where('org_id', $orgId)
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getRecentTrips(string $orgId, int $limit = 50): Collection
    {
        return Trip::where('org_id', $orgId)
            ->with(['vehicle', 'driver', 'corridor'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTripForOrg(string $tripId, string $orgId): Trip
    {
        $trip = Trip::find($tripId);

        if ($trip === null || $trip->org_id !== $orgId) {
            throw new TripNotFoundException($tripId);
        }

        return $trip;
    }

    public function getTripDetailForOrg(string $tripId, string $orgId): Trip
    {
        $trip = $this->getTripForOrg($tripId, $orgId);

        $trip->load([
            'vehicle',
            'driver',
            'corridor',
            'checkpointEvents' => fn ($q) => $q->orderBy('reported_at')->orderBy('created_at'),
            'locationPings' => fn ($q) => $q->orderByDesc('recorded_at')->orderByDesc('created_at'),
        ]);

        return $trip;
    }
}
