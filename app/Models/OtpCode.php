<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class OtpCode extends Model
{
    protected $fillable = ['email', 'code', 'expires_at', 'used_at'];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeValid($query)
    {
        return $query->whereNull('used_at')
                     ->where('expires_at', '>', now());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isUsed(): bool
    {
        return ! is_null($this->used_at);
    }

    public function markUsed(): void
    {
        $this->update(['used_at' => now()]);
    }

    // ─── Factory ──────────────────────────────────────────────────────────────

    /**
     * Generate a new OTP for an email.
     * Deletes all previous unused OTPs for that email first.
     */
    public static function generateFor(string $email): self
    {
        // Hapus OTP lama untuk email ini
        static::where('email', $email)->delete();

        return static::create([
            'email'      => $email,
            'code'       => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(10),
        ]);
    }
}
