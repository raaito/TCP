<?php

namespace App\Console\Commands;

use App\Services\Contracts\GeofenceServiceInterface;
use Illuminate\Console\Command;

class CheckStaleTrips extends Command
{
    protected $signature = 'trips:check-stale
                           {--hours=6 : Trips with no ping in this many hours are flagged delayed}';

    protected $description = 'Flag in-transit/delayed trips that have gone silent (no location ping) as delayed';

    public function handle(GeofenceServiceInterface $geofence): int
    {
        $hours = (int) $this->option('hours');

        $affected = $geofence->markStaleTripsDelayed($hours);

        foreach ($affected as $tripId) {
            $this->info("Flagged trip {$tripId} as delayed (no signal for {$hours}h).");
        }

        if ($affected->isEmpty()) {
            $this->line('No stale trips found.');
        }

        return self::SUCCESS;
    }
}