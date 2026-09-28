<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UserAdminCommand extends Command
{
    protected $signature = 'user:admin
        {email? : Email user yang akan diproses}
        {--revoke : Turunkan admin menjadi user biasa}
        {--list : Tampilkan semua admin}';

    protected $description = 'Jadikan user sebagai admin (atau revoke/list) berdasarkan email';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listAdmins();
        }

        $email = strtolower(trim((string) $this->argument('email')));

        if ($email === '') {
            $this->error('Email wajib diisi. Contoh: php artisan user:admin budi@mail.com');
            return self::FAILURE;
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("User dengan email {$email} tidak ditemukan.");
            return self::FAILURE;
        }

        if ($this->option('revoke')) {
            if (($user->role ?? 'user') !== 'admin') {
                $this->warn("{$user->email} bukan admin (role saat ini: " . ($user->role ?? 'user') . ").");
                return self::SUCCESS;
            }

            $user->role = 'user';
            $user->save();

            $this->info("{$user->email} diturunkan menjadi user biasa.");
            return self::SUCCESS;
        }

        if (($user->role ?? 'user') === 'admin') {
            $this->warn("{$user->email} sudah admin.");
            return self::SUCCESS;
        }

        $user->role = 'admin';
        $user->save();

        $this->info("{$user->email} sekarang admin. Silakan login ulang lalu buka /admin/dashboard.");
        return self::SUCCESS;
    }

    private function listAdmins(): int
    {
        $admins = User::where('role', 'admin')
            ->orderBy('email')
            ->get(['name', 'email', 'created_at']);

        if ($admins->isEmpty()) {
            $this->warn('Belum ada admin.');
            return self::SUCCESS;
        }

        $this->table(
            ['Name', 'Email', 'Created'],
            $admins->map(fn (User $u) => [
                $u->name,
                $u->email,
                $u->created_at?->format('Y-m-d H:i') ?? '-',
            ])->toArray()
        );

        return self::SUCCESS;
    }
}
