<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->string('id_pembayaran', 20)->primary(); // ID unik untuk setiap pembayaran, misal: 'PEM001'
            
            // Foreign key ke tabel pemesanan
            $table->string('pemesanan_id', 20);
            $table->foreign('pemesanan_id')->references('id_pemesanan')->on('pemesanan')->onDelete('cascade');

            // Foreign key ke tabel konsumens (user yang membayar)
            $table->string('konsumen_id');
            $table->foreign('konsumen_id')->references('no_identitas')->on('konsumens')->onDelete('cascade');

            $table->string('metode_pembayaran'); // misal: 'saldo', 'transfer_bank'
            $table->decimal('total_pembayaran', 15, 2); // Jumlah yang harus dibayar
            
            // Status pembayaran: 'pending', 'berhasil', 'gagal'
            $table->enum('status', ['pending', 'berhasil', 'gagal'])->default('pending');
            
            $table->timestamp('tanggal_pembayaran')->nullable(); // Diisi hanya jika pembayaran berhasil
            
            $table->timestamps(); // created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};