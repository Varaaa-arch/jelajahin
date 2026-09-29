<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $primaryKey = 'key';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    /**
     * Ambil nilai setting dengan cache 1 jam.
     * Aman dipanggil sebelum tabel ada (fallback default).
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $default;
            }

            return Cache::remember("settings.{$key}", 3600, function () use ($key, $default) {
                return static::where('key', $key)->value('value') ?? $default;
            });
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("settings.{$key}");
    }

    public static function taxRate(): float
    {
        return ((float) static::get('tax_rate', 10)) / 100;
    }
}
