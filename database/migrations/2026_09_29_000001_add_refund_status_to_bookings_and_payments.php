<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BOOKING_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled', 'refund_requested', 'refunded'];
    private const PAYMENT_STATUSES = ['pending', 'success', 'failed', 'expired', 'deny', 'refunded'];

    private const OLD_BOOKING_STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];
    private const OLD_PAYMENT_STATUSES = ['pending', 'success', 'failed', 'expired', 'deny'];

    public function up(): void
    {
        $this->sync(self::BOOKING_STATUSES, self::PAYMENT_STATUSES);
    }

    public function down(): void
    {
        $this->sync(self::OLD_BOOKING_STATUSES, self::OLD_PAYMENT_STATUSES);
    }

    /**
     * Sinkron daftar nilai CHECK constraint status.
     * - pgsql: enum() Laravel dibuat sebagai CHECK constraint -> drop & tambah ulang.
     * - mysql: ubah definisi ENUM via MODIFY COLUMN.
     * - sqlite (dipakai test): enum dibuat sebagai varchar + CHECK inline,
     *     sehingga kolom diubah ke string via doctrine/dbal (rebuild tabel).
     */
    private function sync(array $bookingStatuses, array $paymentStatuses): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            $bookingList = $this->quoteList($bookingStatuses);
            $paymentList = $this->quoteList($paymentStatuses);
            DB::statement('ALTER TABLE bookings DROP CONSTRAINT IF EXISTS bookings_status_check');
            DB::statement("ALTER TABLE bookings ADD CONSTRAINT bookings_status_check CHECK (status::text = ANY (ARRAY[{$bookingList}]::text[]))");
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS payments_status_check');
            DB::statement("ALTER TABLE payments ADD CONSTRAINT payments_status_check CHECK (status::text = ANY (ARRAY[{$paymentList}]::text[]))");

            return;
        }

        if ($driver === 'mysql') {
            $bookingEnum = $this->enumList($bookingStatuses);
            $paymentEnum = $this->enumList($paymentStatuses);
            DB::statement("ALTER TABLE bookings MODIFY status ENUM({$bookingEnum}) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE payments MODIFY status ENUM({$paymentEnum}) NOT NULL DEFAULT 'pending'");

            return;
        }

        // sqlite (dipakai test): enum dibuat sebagai varchar + CHECK inline.
        // doctrine/dbal me-rebuild tabel sehingga CHECK lama ikut terbuang;
        // validasi nilai tetap dijaga di level aplikasi (FormRequest / in:).
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
        Schema::table('payments', function (Blueprint $table) {
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
