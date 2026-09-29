<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Aircraft extends Model
{
    use HasUuids;

    protected $table = 'aircraft';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'aircraft_type_id', 'airline_id', 'registration_number', 'manufacture_year', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function aircraftType()
    {
        return $this->belongsTo(AircraftType::class, 'aircraft_type_id');
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }
}
