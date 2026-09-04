<?php

namespace Database\Seeders;

use App\Enums\CheckpointSource;
use App\Enums\DelayReason;
use App\Enums\OrgType;
use App\Enums\TripStatus;
use App\Models\CheckpointEvent;
use App\Models\ClientOrg;
use App\Models\Corridor;
use App\Models\DispatcherUser;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class TradeCorridorSeeder extends Seeder
{
    public function run(): void
    {
        $corridor = Corridor::create([
            'name' => 'Lagos–Accra',
            'origin' => 'Lagos, Nigeria',
            'destination' => 'Accra, Ghana',
            'waypoints' => ['Cotonou', 'Lomé', 'Aflao'],
        ]);

        $org = ClientOrg::create([
            'name' => 'West Coast Logistics Coop',
            'type' => OrgType::Cooperative,
            'corridor_id' => $corridor->id,
        ]);

        $dispatcher = DispatcherUser::create([
            'org_id' => $org->id,
            'name' => 'James Dispatcher',
            'phone_number' => '+2348010000001',
            'role' => 'dispatcher',
            'password' => 'password',
        ]);

        $vehicles = collect([
            Vehicle::create([
                'org_id' => $org->id,
                'plate_number' => 'LAG-458-AK',
                'capacity_type' => '10-ton truck',
            ]),
            Vehicle::create([
                'org_id' => $org->id,
                'plate_number' => 'ACC-723-BM',
                'capacity_type' => '20-ton truck',
            ]),
        ]);

        $drivers = collect([
            Driver::create([
                'org_id' => $org->id,
                'name' => 'Musa Bello',
                'phone_number' => '+2348020000001',
            ]),
            Driver::create([
                'org_id' => $org->id,
                'name' => 'Kwame Asante',
                'phone_number' => '+233500000001',
            ]),
        ]);

        // Accra central market coords — used to exercise geofence on the ping flow.
        $accraLat = 5.5500;
        $accraLng = -0.2174;

        $trip = Trip::create([
            'org_id' => $org->id,
            'vehicle_id' => $vehicles->first()->id,
            'driver_id' => $drivers->first()->id,
            'corridor_id' => $corridor->id,
            'cargo_type' => 'rice (50kg bags)',
            'departure_time' => now()->subHours(20),
            'expected_arrival' => now()->subHours(2),
            'destination_lat' => $accraLat,
            'destination_lng' => $accraLng,
            'geofence_radius_m' => 200,
            'status' => TripStatus::InTransit,
            'auto_closed' => false,
            'created_by' => $dispatcher->id,
        ]);

        CheckpointEvent::create([
            'trip_id' => $trip->id,
            'checkpoint_name' => 'Departed Lagos',
            'source' => CheckpointSource::Dispatcher,
            'delay_flag' => false,
        ]);

        CheckpointEvent::create([
            'trip_id' => $trip->id,
            'checkpoint_name' => 'Cotonou border',
            'source' => CheckpointSource::WhatsApp,
            'delay_flag' => true,
            'delay_reason' => DelayReason::Customs,
        ]);
    }
}
