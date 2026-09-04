<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispatcher_users', function (Blueprint $table) {
            $table->string('password')->nullable()->after('phone_number');
            $table->timestampTz('email_verified_at')->nullable()->after('password');
        });

        // phone_number is the login identity for dispatchers
        Schema::table('dispatcher_users', function (Blueprint $table) {
            $table->unique('phone_number', 'dispatcher_users_phone_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('dispatcher_users', function (Blueprint $table) {
            $table->dropUnique('dispatcher_users_phone_number_unique');
        });

        Schema::table('dispatcher_users', function (Blueprint $table) {
            $table->dropColumn(['password', 'email_verified_at']);
        });
    }
};
