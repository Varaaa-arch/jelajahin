<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AircraftType extends Model
{
    use HasUuids;

    protected $table = 'aircraft_types';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'manufacturer', 'model', 'total_seats'];
}
