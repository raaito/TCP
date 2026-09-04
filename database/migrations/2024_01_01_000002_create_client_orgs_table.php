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
            DB::statement("CREATE TYPE org_type AS ENUM ('cooperative', 'distributor', 'warehouse')");
        }

        Schema::create('client_orgs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type');
            $table->foreignUuid('corridor_id')->constrained('corridors');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_orgs');

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP TYPE IF EXISTS org_type');
        }
    }
};
