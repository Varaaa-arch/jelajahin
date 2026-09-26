<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SeatClass extends Model
{
    use HasUuids;

    protected $table = 'seat_classes';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['id', 'name', 'display_name', 'baggage_allowance_kg'];
}
