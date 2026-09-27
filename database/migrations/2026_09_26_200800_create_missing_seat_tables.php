<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // aircraft_types
        if (! Schema::hasTable('aircraft_types')) {
            Schema::create('aircraft_types', function (Blueprint $table) {
                // UUID dibuat oleh model HasUuids di PHP — tanpa default DB agar jalan di pgsql + sqlite
                $table->uuid('id')->primary();
                $table->string('name', 100)->unique();
                $table->string('manufacturer', 100)->nullable();
                $table->string('model', 100)->nullable();
                $table->integer('total_seats');
                $table->timestampTz('created_at')->useCurrent();
            });
        }

        // seat_classes
        if (! Schema::hasTable('seat_classes')) {
            Schema::create('seat_classes', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name', 50)->unique();
                $table->string('display_name', 100)->nullable();
                $table->integer('baggage_allowance_kg')->default(20);
                $table->timestampTz('created_at')->useCurrent();
            });
            DB::table('seat_classes')->insert([
                ['id' => (string) Str::uuid(), 'name' => 'economy', 'display_name' => 'Economy', 'baggage_allowance_kg' => 20],
                ['id' => (string) Str::uuid(), 'name' => 'premium_economy', 'display_name' => 'Premium Economy', 'baggage_allowance_kg' => 25],
                ['id' => (string) Str::uuid(), 'name' => 'business', 'display_name' => 'Business', 'baggage_allowance_kg' => 30],
                ['id' => (string) Str::uuid(), 'name' => 'first', 'display_name' => 'First', 'baggage_allowance_kg' => 40],
            ]);
        }

        // aircraft
        if (! Schema::hasTable('aircraft')) {
            Schema::create('aircraft', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('aircraft_type_id');
                $table->uuid('airline_id');
                $table->string('registration_number', 20)->unique();
                $table->integer('manufacture_year')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();

                $table->foreign('aircraft_type_id')->references('id')->on('aircraft_types')->onDelete('cascade');
                $table->foreign('airline_id')->references('id')->on('airlines')->onDelete('cascade');
            });
        }

        // aircraft_seats
        if (! Schema::hasTable('aircraft_seats')) {
            Schema::create('aircraft_seats', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('aircraft_type_id');
                $table->uuid('seat_class_id');
                $table->string('seat_number', 10);
                $table->integer('row_number');
                $table->string('column_letter', 1);
                $table->boolean('is_exit_row')->default(false);

                $table->foreign('aircraft_type_id')->references('id')->on('aircraft_types')->onDelete('cascade');
                $table->foreign('seat_class_id')->references('id')->on('seat_classes')->onDelete('cascade');
                $table->unique(['aircraft_type_id', 'seat_number']);
            });
        }

        // flight_seats
        if (! Schema::hasTable('flight_seats')) {
            Schema::create('flight_seats', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('flight_id');
                $table->uuid('aircraft_seat_id');
                $table->decimal('current_price', 12, 2);
                $table->boolean('is_available')->default(true);
                $table->uuid('booking_id')->nullable();
                $table->timestampTz('created_at')->useCurrent();
                $table->timestampTz('updated_at')->useCurrent();

                $table->foreign('flight_id')->references('id')->on('flights')->onDelete('cascade');
                $table->foreign('aircraft_seat_id')->references('id')->on('aircraft_seats')->onDelete('cascade');
                $table->unique(['flight_id', 'aircraft_seat_id']);
                $table->index('flight_id');
                $table->index('is_available');
            });
        }

        // pgcrypto hanya untuk pgsql produksi — lewati saat testing sqlite
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('flight_seats');
        Schema::dropIfExists('aircraft_seats');
        Schema::dropIfExists('aircraft');
        Schema::dropIfExists('seat_classes');
        Schema::dropIfExists('aircraft_types');
    }
};
