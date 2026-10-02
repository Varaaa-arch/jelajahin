<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Hapus akun inactive yang tidak pernah verifikasi OTP setiap 30 menit
Schedule::command('users:purge-unverified')->everyThirtyMinutes();
