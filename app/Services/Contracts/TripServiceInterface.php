<?php

namespace App\Services\Contracts;

use App\DTOs\CreateTripData;
use App\DTOs\LogCheckpointData;
use App\DTOs\UpdateTripData;
use App\Enums\TripStatus;
use App\Models\CheckpointEvent;
use App\Models\Trip;
use Illuminate\Support\Collection;

interface TripServiceInterface
{
    public function createTrip(CreateTripData $data): Trip;

    public function updateTrip(string $tripId, string $orgId, UpdateTripData $data): Trip;

    public function logCheckpoint(string $tripId, LogCheckpointData $data): CheckpointEvent;

    public function closeTripManually(string $tripId, string $reason): Trip;

    public function updateTripStatus(string $tripId, TripStatus $status): Trip;

    public function getActiveTrips(string $orgId): Collection;

    /**
     * Drivers in an org who do NOT currently have an active (created/in_transit/delayed)
     * trip. Used to populate the create-trip driver dropdown so dispatchers can't pick
     * a driver who is already on the road.
     */
    public function getAvailableDrivers(string $orgId): Collection;

    /**
     * Drivers selectable when editing a trip: the org's available drivers, plus the
     * trip's current driver (who would otherwise be hidden because they're on this
     * active trip). Throws TripNotFoundException if the trip is not in the org.
     */
    public function getDriversForTripEdit(string $tripId, string $orgId): Collection;

    /**
     * Most recent trips for an org (active + recently closed), eager-loaded with
     * vehicle/driver/corridor. Used by the dashboard status board.
     */
    public function getRecentTrips(string $orgId, int $limit = 50): Collection;

    /**
     * Fetch a single trip scoped to the given org. Throws TripNotFoundException
     * if the trip does not exist OR belongs to a different org — this is the
     * org-scope guard every controller mutation should route through.
     */
    public function getTripForOrg(string $tripId, string $orgId): Trip;

    /**
     * Fetch a single trip scoped to the given org, eager-loaded with the
     * detail-page relations (checkpoint timeline + location pings + vehicle/
     * driver/corridor). Throws TripNotFoundException if not found or wrong org.
     */
    public function getTripDetailForOrg(string $tripId, string $orgId): Trip;
}
