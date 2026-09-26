<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AircraftSeat extends Model
{
    use HasUuids;

    protected $table = 'aircraft_seats';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'aircraft_type_id', 'seat_class_id', 'seat_number', 'row_number', 'column_letter', 'is_exit_row'];

    protected $casts = ['is_exit_row' => 'boolean'];

    public function seatClass()
    {
        return $this->belongsTo(SeatClass::class, 'seat_class_id');
    }

    public function aircraftType()
    {
        return $this->belongsTo(AircraftType::class, 'aircraft_type_id');
    }
}
