<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightSeat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'flight_seats';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'flight_id',
        'aircraft_seat_id',
        'current_price',
        'is_available',
        'booking_id',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'current_price' => 'float',
    ];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function aircraftSeat()
    {
        return $this->belongsTo(AircraftSeat::class);
    }

    // Expose seat_number via aircraft_seat relation
    public function getSeatNumberAttribute(): string
    {
        return $this->aircraftSeat?->seat_number ?? '-';
    }
}
