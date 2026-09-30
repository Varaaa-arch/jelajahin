<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * users.id bertipe UUID string, sedangkan morphs() bawaan Sanctum
     * membuat tokenable_id BIGINT -> createToken selalu gagal di Postgres.
     * Ubah kolom menjadi UUID agar cocok dengan users.id.
     */
    public function up(): void
    {
        // Tabel masih kosong / tidak ada token valid (id integer tak pernah
        // cocok dengan UUID users), jadi aman dikosongkan dulu.
        DB::table('personal_access_tokens')->delete();

        DB::statement(
            'ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE uuid USING NULL'
        );

        // Pastikan index morph tetap ada (nama bawaan Laravel).
        if (! $this->indexExists('personal_access_tokens_tokenable_type_tokenable_id_index')) {
            Schema::table('personal_access_tokens', function ($table) {
                $table->index(['tokenable_type', 'tokenable_id']);
            });
        }
    }

    public function down(): void
    {
        DB::table('personal_access_tokens')->delete();

        DB::statement(
            'ALTER TABLE personal_access_tokens ALTER COLUMN tokenable_id TYPE bigint USING NULL'
        );
    }

    private function indexExists(string $name): bool
    {
        return collect(DB::select(
            'SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?',
            ['personal_access_tokens', $name]
        ))->isNotEmpty();
    }
};
