<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'invoices';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'booking_id',
        'invoice_number',
        'issued_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'invoice_url',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this -> belongsTo(Booking::class);
    }
}