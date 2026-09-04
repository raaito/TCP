<?php

namespace App\Services;

use App\Enums\CheckpointSource;
use App\Enums\TripStatus;
use App\Events\TripArrived;
use App\Events\TripDelayed;
use App\Models\CheckpointEvent;
use App\Models\LocationPing;
use App\Models\Trip;
use App\Services\Contracts\GeofenceServiceInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class GeofenceService implements GeofenceServiceInterface
{
    public function haversineM(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusM = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2.0) * sin($dLat / 2.0)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2.0) * sin($dLng / 2.0);

        $c = 2.0 * atan2(sqrt($a), sqrt(1.0 - $a));

        return $earthRadiusM * $c;
    }

    public function evaluatePing(LocationPing $ping): Trip
    {
        return DB::transaction(function () use ($ping) {
            $trip = Trip::lockForUpdate()->findOrFail($ping->trip_id);

            $trip->update(['last_ping_at' => $ping->recorded_at]);

            if ($trip->destination_lat === null || $trip->destination_lng === null) {
                return $trip->refresh();
            }

            if (in_array($trip->status, [TripStatus::Arrived, TripStatus::Cancelled], true)) {
                return $trip->refresh();
            }

            $distance = $this->haversineM(
                $ping->lat,
                $ping->lng,
                (float) $trip->destination_lat,
                (float) $trip->destination_lng,
            );

            if ($distance <= (float) $trip->geofence_radius_m) {
                $trip->update([
                    'status' => TripStatus::Arrived,
                    'auto_closed' => true,
                ]);

                CheckpointEvent::create([
                    'trip_id' => $trip->id,
                    'checkpoint_name' => 'Destination (auto-detected via geofence)',
                    'source' => CheckpointSource::System,
                    'delay_flag' => false,
                ]);

                event(new TripArrived($trip->refresh(), 'geofence'));
            }

            return $trip->refresh();
        });
    }

    public function staleTrips(int $staleAfterHours = 6): Collection
    {
        $cutoff = now()->subHours($staleAfterHours);

        $trips = Trip::query()
            ->whereIn('status', [TripStatus::InTransit, TripStatus::Delayed])
            ->where('last_ping_at', '<', $cutoff)
            ->whereNotNull('destination_lat')
            ->whereNotNull('last_ping_at')
            ->with(['locationPings' => function ($q) {
                $q->orderByDesc('recorded_at')->limit(1);
            }])
            ->get();

        return $trips->map(function (Trip $trip) use ($staleAfterHours) {
            $lastPing = $trip->locationPings->first();
            $distance = null;

            if ($lastPing !== null) {
                $distance = $this->haversineM(
                    (float) $lastPing->lat,
                    (float) $lastPing->lng,
                    (float) $trip->destination_lat,
                    (float) $trip->destination_lng,
                );
            }

            return [
                'trip_id' => $trip->id,
                'org_id' => $trip->org_id,
                'hours_since_last_ping' => round(
                    $trip->last_ping_at->diffInMinutes(now()) / 60.0,
                    1,
                ),
                'last_known_distance_m' => $distance,
            ];
        });
    }

    public function markStaleTripsDelayed(int $staleAfterHours = 6): Collection
    {
        $affected = [];

        foreach ($this->staleTrips($staleAfterHours) as $row) {
            $trip = Trip::find($row['trip_id']);

            if ($trip === null || $trip->status === TripStatus::Delayed) {
                continue;
            }

            $trip->update(['status' => TripStatus::Delayed]);

            $message = 'No signal for '.$row['hours_since_last_ping'].'h';

            if ($row['last_known_distance_m'] !== null) {
                $message .= ', last seen ~'.round($row['last_known_distance_m'] / 1000, 1).'km from destination';
            }

            CheckpointEvent::create([
                'trip_id' => $trip->id,
                'checkpoint_name' => $message,
                'source' => CheckpointSource::System,
                'delay_flag' => true,
            ]);

            event(new TripDelayed($trip, $row['hours_since_last_ping'], $row['last_known_distance_m']));

            $affected[] = $trip->id;
        }

        return collect($affected);
    }
}
