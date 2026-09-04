<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE TYPE trip_status AS ENUM ('created', 'in_transit', 'delayed', 'arrived', 'cancelled')");
        }

        Schema::create('trips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('client_orgs');
            $table->foreignUuid('vehicle_id')->nullable()->constrained('vehicles');
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers');
            $table->foreignUuid('corridor_id')->constrained('corridors');
            $table->string('cargo_type')->nullable();
            $table->timestampTz('departure_time')->nullable();
            $table->timestampTz('expected_arrival')->nullable();
            $table->string('status')->default('created');
            $table->float('destination_lat')->nullable();
            $table->float('destination_lng')->nullable();
            $table->integer('geofence_radius_m')->default(200);
            $table->timestampTz('last_ping_at')->nullable();
            $table->boolean('auto_closed')->default(false);
            $table->foreignUuid('created_by')->nullable()->constrained('dispatcher_users');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX idx_one_active_trip_per_driver ON trips(driver_id) WHERE status IN ('created', 'in_transit', 'delayed') AND driver_id IS NOT NULL");
        } else {
            // SQLite has no partial-unique-index support via raw easily; mirror the invariant in app logic.
            // Eloquent-level enforcement lives in TripService::createTrip via the existing one-active-trip rule.
        }
        DB::statement('CREATE INDEX idx_trips_org_status ON trips(org_id, status)');
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS trip_status');
        }
    }
};
