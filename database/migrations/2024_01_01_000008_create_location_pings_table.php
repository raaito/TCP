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
            DB::statement("CREATE TYPE ping_source AS ENUM ('driver_phone', 'dispatcher_phone', 'agent_relay')");
        }

        Schema::create('location_pings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->float('lat');
            $table->float('lng');
            $table->timestampTz('recorded_at')->useCurrent();
            $table->string('source');
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_location_pings_trip ON location_pings(trip_id, recorded_at DESC)');
    }

    public function down(): void
    {
        Schema::dropIfExists('location_pings');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS ping_source');
        }
    }
};
