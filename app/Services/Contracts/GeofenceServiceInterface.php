<?php

namespace App\Services\Contracts;

use App\Models\LocationPing;
use App\Models\Trip;
use Illuminate\Support\Collection;

interface GeofenceServiceInterface
{
    /**
     * Great-circle distance in meters between two lat/lng points.
     */
    public function haversineM(float $lat1, float $lng1, float $lat2, float $lng2): float;

    /**
     * Evaluate a freshly-recorded LocationPing against its trip's geofence.
     *
     * Behaviour per MVP plan §4D:
     *  - overlap is the ONLY thing that closes a trip — never silence, never a timer
     *  - on overlap (and trip not already arrived/cancelled): set status=arrived,
     *    auto_closed=true, log a system CheckpointEvent
     *  - on no overlap: just update last_ping_at — never a status change
     *  - returns the Trip (refreshed) regardless of outcome
     */
    public function evaluatePing(LocationPing $ping): Trip;

    /**
     * Find in_transit/delayed trips whose last_ping_at is older than $staleAfterHours.
     *
     * Reads-only. Never auto-closes anything. Each row carries the last computed
     * distance to destination so the alert can read e.g.
     * "no signal for 6hrs, last seen ~42km from Accra" instead of a bare "no signal".
     *
     * @return Collection<int, array{
     *   trip_id: string,
     *   org_id: string,
     *   hours_since_last_ping: float,
     *   last_known_distance_m: float|null
     * }>
     */
    public function staleTrips(int $staleAfterHours = 6): Collection;

    /**
     * Flag stale in-transit/delayed trips as `delayed`.
     *
     * Uses staleTrips() output, flips each trip's status to `delayed`, logs a
     * system-sourced checkpoint event with the silence + distance context, and
     * dispatches TripDelayed. Never auto-closes. Returns affected trip IDs.
     *
     * @return Collection<int, string>
     */
    public function markStaleTripsDelayed(int $staleAfterHours = 6): Collection;
}
