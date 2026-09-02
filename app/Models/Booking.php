<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'pnr_code',
        'user_id',
        'flight_id',
        'promo_id',
        'base_amount',
        'discount_amount',
        'tax_amount',
        'total_price',
        'passenger_count',
        'status',
        'special_requests',
    ];

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    public function flight(): BelongsTo
    {
        return $this->belongsTo(Flight::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
