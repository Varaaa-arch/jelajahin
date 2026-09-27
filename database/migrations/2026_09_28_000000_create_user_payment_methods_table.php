<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            // bank_account | e_wallet | card
            $table->string('type', 20);
            // bca/bni/bri/mandiri | gopay/ovo/dana/shopeepay | visa/mastercard/amex
            $table->string('provider', 30);
            $table->string('label', 100)->nullable();
            $table->string('account_name', 100);
            // Nomor tersimpan terenkripsi (model cast), kartu hanya last4
            $table->text('account_number');
            $table->string('expiry', 5)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_payment_methods');
    }
};
