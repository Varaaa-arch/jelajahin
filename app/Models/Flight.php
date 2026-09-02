<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'route_id',
        'aircraft_id',
        'flight_number',
        'departure_date',
        'departure_time',
        'arrival_time',
        'base_price',
        'tax_surcharge',
        'fuel_surcharge',
        'status',
        'seats_available',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'base_price' => 'float',
    ];
}
