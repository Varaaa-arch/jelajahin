<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // aircraft_types
        if (! Schema::hasTable('aircraft_types')) {
            Schema::create('aircraft_types', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->string('name', 100)->unique();
                $table->string('manufacturer', 100)->nullable();
                $table->string('model', 100)->nullable();
                $table->integer('total_seats');
                $table->timestampTz('created_at')->useCurrent();
            });
            DB::statement('ALTER TABLE aircraft_types ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        }

        // seat_classes
        if (! Schema::hasTable('seat_classes')) {
            Schema::create('seat_classes', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->string('name', 50)->unique();
                $table->string('display_name', 100)->nullable();
                $table->integer('baggage_allowance_kg')->default(20);
                $table->timestampTz('created_at')->useCurrent();
            });
            DB::statement('ALTER TABLE seat_classes ALTER COLUMN id SET DEFAULT gen_random_uuid()');
            DB::table('seat_classes')->insert([
                ['name' => 'economy', 'display_name' => 'Economy', 'baggage_allowance_kg' => 20],
                ['name' => 'premium_economy', 'display_name' => 'Premium Economy', 'baggage_allowance_kg' => 25],
                ['name' => 'business', 'display_name' => 'Business', 'baggage_allowance_kg' => 30],
                ['name' => 'first', 'display_name' => 'First', 'baggage_allowance_kg' => 40],
            ]);
        }

        // aircraft
        if (! Schema::hasTable('aircraft')) {
            Schema::create('aircraft', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
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
            DB::statement('ALTER TABLE aircraft ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        }

        // aircraft_seats
        if (! Schema::hasTable('aircraft_seats')) {
            Schema::create('aircraft_seats', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
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
            DB::statement('ALTER TABLE aircraft_seats ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        }

        // flight_seats
        if (! Schema::hasTable('flight_seats')) {
            Schema::create('flight_seats', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
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
            DB::statement('ALTER TABLE flight_seats ALTER COLUMN id SET DEFAULT gen_random_uuid()');
        }

        // ensure uuid extension for gen_random_uuid
        DB::statement('CREATE EXTENSION IF NOT EXISTS "pgcrypto"');
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
