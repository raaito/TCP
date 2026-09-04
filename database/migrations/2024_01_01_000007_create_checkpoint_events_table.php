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
            DB::statement("CREATE TYPE checkpoint_source AS ENUM ('dispatcher', 'whatsapp', 'ussd_relay', 'agent', 'system')");
            DB::statement("CREATE TYPE delay_reason AS ENUM ('customs', 'mechanical', 'security', 'traffic', 'other')");
        }

        Schema::create('checkpoint_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('trip_id')->constrained('trips')->cascadeOnDelete();
            $table->string('checkpoint_name')->nullable();
            $table->timestampTz('reported_at')->useCurrent();
            $table->string('source');
            $table->boolean('delay_flag')->default(false);
            $table->string('delay_reason')->nullable();
            $table->timestamps();
        });

        DB::statement('CREATE INDEX idx_checkpoint_events_trip ON checkpoint_events(trip_id, reported_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('checkpoint_events');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS checkpoint_source');
            DB::statement('DROP TYPE IF EXISTS delay_reason');
        }
    }
};
