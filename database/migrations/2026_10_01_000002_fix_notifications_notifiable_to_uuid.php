<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel notifications dibuat dengan $table->morphs() sehingga
     * notifiable_id bertipe bigint, padahal users.id adalah UUID.
     * Akibatnya SEMUA notifikasi channel database gagal dengan
     * "invalid input syntax for type bigint". Ubah ke uuid.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->uuidMorphs('notifiable');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->morphs('notifiable');
        });
    }
};
