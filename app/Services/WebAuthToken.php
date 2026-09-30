<?php

namespace App\Services;

use App\Models\User;

/**
 * Token Sanctum untuk sesi web (first-party SPA).
 *
 * Memungkinkan frontend Inertia/axios memanggil routes/api.php yang
 * dilindungi auth:sanctum memakai header Bearer, padahal login web
 * memakai session cookie (grup middleware api tidak punya session,
 * sehingga fallback session Sanctum tidak bisa bekerja).
 */
class WebAuthToken
{
    public const TOKEN_NAME = 'web-token';

    public const TTL_DAYS = 30;

    /**
     * Terbitkan token baru; token web lama user tsb diprune agar
     * tabel tidak menumpuk (maks 1 token web aktif per user).
     */
    public static function issue(User $user): string
    {
        $user->tokens()
            ->where('name', self::TOKEN_NAME)
            ->delete();

        return $user->createToken(
            self::TOKEN_NAME,
            ['*'],
            now()->addDays(self::TTL_DAYS)
        )->plainTextToken;
    }

    /**
     * Cabut semua token web milik user (dipakai saat logout).
     */
    public static function revoke(User $user): void
    {
        $user->tokens()->where('name', self::TOKEN_NAME)->delete();
    }
}
