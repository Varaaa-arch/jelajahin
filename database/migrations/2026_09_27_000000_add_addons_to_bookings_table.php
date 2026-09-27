<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'addons')) {
                $table->jsonb('addons')->nullable()->after('special_requests');
            }
            if (! Schema::hasColumn('bookings', 'addons_amount')) {
                $table->decimal('addons_amount', 12, 2)->default(0)->after('addons');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['addons', 'addons_amount']);
        });
    }
};
