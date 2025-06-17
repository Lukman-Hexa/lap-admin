<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Mengubah kolom agar boleh kosong (nullable)
            $table->string('metode_pembayaran')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika migrasi di-rollback
            $table->string('metode_pembayaran')->nullable(false)->change();
        });
    }
};