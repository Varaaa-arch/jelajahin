<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('key', 100)->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        $defaults = [
            'site_name' => 'Jelajahi',
            'support_email' => 'support@jelajahin.id',
            'tax_rate' => '10',
            'announcement' => '',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], [
                'value' => $value,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
