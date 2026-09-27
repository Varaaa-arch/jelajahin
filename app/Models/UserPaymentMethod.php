<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPaymentMethod extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    public const TYPES = ['bank_account', 'e_wallet', 'card'];

    public const PROVIDERS = [
        'bank_account' => ['bca', 'bni', 'bri', 'mandiri'],
        'e_wallet' => ['gopay', 'ovo', 'dana', 'shopeepay'],
        'card' => ['visa', 'mastercard', 'amex'],
    ];

    protected $fillable = [
        'user_id',
        'type',
        'provider',
        'label',
        'account_name',
        'account_number',
        'expiry',
        'is_default',
    ];

    protected $casts = [
        // Terenkripsi di DB, otomatis dekrip saat diakses
        'account_number' => 'encrypted',
        'is_default' => 'boolean',
    ];

    protected $hidden = [
        // Jangan pernah bocorkan nomor mentah ke JSON
        'account_number',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Nomor tampil: kartu → "•••• last4", lainnya → sensor tengah.
     */
    public function maskedNumber(): string
    {
        // Cast 'encrypted' memakai encrypt/decrypt TANPA serialize,
        // jadi baca lewat cast (jangan decrypt() manual yang mewajibkan serialize).
        try {
            $decrypted = (string) $this->account_number;
        } catch (\Throwable) {
            $decrypted = '';
        }

        if ($this->type === 'card') {
            return '•••• •••• •••• ' . substr($decrypted, -4);
        }

        $len = strlen($decrypted);
        if ($len <= 6) {
            return str_repeat('•', max($len, 4));
        }

        return substr($decrypted, 0, 4) . str_repeat('•', 4) . substr($decrypted, -4);
    }
}
