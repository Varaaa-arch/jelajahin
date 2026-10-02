<?php

namespace App\Console\Commands;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Console\Command;

class PurgeUnverifiedUsers extends Command
{
    protected $signature   = 'users:purge-unverified';
    protected $description = 'Hapus akun inactive yang OTP-nya sudah expired (tidak pernah verifikasi email)';

    public function handle(): int
    {
        // Akun dianggap "abandoned" jika:
        //   1. status = inactive (belum verifikasi)
        //   2. email_verified_at = null
        //   3. dibuat lebih dari 15 menit lalu (beri jeda melebihi masa berlaku OTP 10 menit)
        //   4. tidak punya OTP yang masih valid
        $cutoff = now()->subMinutes(15);

        $abandoned = User::where('status', 'inactive')
            ->whereNull('email_verified_at')
            ->where('created_at', '<=', $cutoff)
            ->whereNotExists(function ($query) {
                $query->from('otp_codes')
                    ->whereColumn('otp_codes.email', 'users.email')
                    ->whereNull('otp_codes.used_at')
                    ->where('otp_codes.expires_at', '>', now());
            })
            ->get();

        if ($abandoned->isEmpty()) {
            $this->info('Tidak ada akun tidak terverifikasi yang perlu dihapus.');
            return self::SUCCESS;
        }

        $emails = $abandoned->pluck('email')->toArray();

        // Hapus OTP terkait terlebih dahulu
        OtpCode::whereIn('email', $emails)->delete();

        // Hapus user
        $count = User::whereIn('email', $emails)->delete();

        $this->info("Berhasil menghapus {$count} akun tidak terverifikasi: " . implode(', ', $emails));

        return self::SUCCESS;
    }
}
