<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('route_id');
            $table->uuid('aircraft_id')->nullable();
            $table->string('flight_number')->unique();
            $table->date('departure_date');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('tax_surcharge', 15, 2)->default(0);
            $table->decimal('fuel_surcharge', 15, 2)->default(0);
            $table->enum('status', ['scheduled', 'boarding', 'departed', 'arrived', 'cancelled'])->default('scheduled');
            $table->unsignedInteger('seats_available')->default(0);
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->index('departure_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};