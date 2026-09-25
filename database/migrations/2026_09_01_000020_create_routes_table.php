<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('airline_id');
            $table->uuid('origin_airport_id');
            $table->uuid('destination_airport_id');
            $table->string('flight_number_prefix', 3)->nullable();
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('airline_id')->references('id')->on('airlines')->onDelete('cascade');
            $table->foreign('origin_airport_id')->references('id')->on('airports')->onDelete('cascade');
            $table->foreign('destination_airport_id')->references('id')->on('airports')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};