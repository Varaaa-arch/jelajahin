<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ETicket extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'etickets';  // ADD THIS LINE
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'eticket_number',
        'passenger_name',
        'flight_number',
        'departure_date',
        'departure_time',
        'seat_number',
        'pdf_path',
        'is_sent',
        'sent_at',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
