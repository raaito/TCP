<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->constrained('client_orgs');
            $table->string('plate_number');
            $table->string('capacity_type')->nullable();
            $table->timestampsTz(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
