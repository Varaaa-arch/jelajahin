<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const NEW_BOOKING_STATUSES = [
        'pending',
        'awaiting_confirmation',
        'confirmed',
        'completed',
        'cancelled',
        'refund_requested',
        'refunded',
    ];

    private const OLD_BOOKING_STATUSES = [
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'refund_requested',
        'refunded',
    ];

    public function up(): void
    {
        $this->sync(self::NEW_BOOKING_STATUSES);
    }

    public function down(): void
    {
        $this->sync(self::OLD_BOOKING_STATUSES);
    }

    /**
     * Sinkron daftar nilai CHECK constraint status bookings.
     * - pgsql: enum() Laravel dibuat sebagai CHECK constraint -> drop & tambah ulang.
     * - mysql: ubah definisi ENUM via MODIFY COLUMN.
     * - sqlite (dipakai test): enum dibuat sebagai varchar + CHECK inline,
     *     sehingga kolom diubah ke string via doctrine/dbal (rebuild tabel).
     */
    private function sync(array $bookingStatuses): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $list = $this->quoteList($bookingStatuses);
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status::text = ANY (ARRAY[{$list}]::text[]))");

            return;
        }

        if ($driver === 'mysql') {
            $enum = $this->enumList($bookingStatuses);
            DB::statement("ALTER TABLE bookings MODIFY status ENUM({$enum}) NOT NULL DEFAULT 'pending'");

            return;
        }

        // sqlite (dipakai test): enum dibuat sebagai varchar + CHECK inline.
        // doctrine/dbal me-rebuild tabel sehingga CHECK lama ikut terbuang;
        // validasi nilai tetap dijaga di level aplikasi.
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
    }

    private function quoteList(array $values): string
    {
        return implode(',', array_map(fn ($v) => "'".str_replace("'", "''", $v)."'", $values));
    }

    private function enumList(array $values): string
    {
        return implode(',', array_map(fn ($v) => "'".str_replace("'", "\\'", $v)."'", $values));
    }
};