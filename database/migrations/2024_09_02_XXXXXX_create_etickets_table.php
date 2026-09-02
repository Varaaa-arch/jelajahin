<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('etickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('booking_id');
            $table->string('eticket_number')->unique();
            $table->string('passenger_name');
            $table->string('flight_number');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->string('seat_number')->nullable();
            $table->string('pdf_path')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->index('eticket_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etickets');
    }
};
