<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('org_id')->nullable()->constrained('client_orgs');
            $table->string('name');
            $table->string('phone_number')->unique();
            $table->timestampsTz(0);
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
