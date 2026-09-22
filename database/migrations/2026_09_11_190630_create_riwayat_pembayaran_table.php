<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembayaran_id')->constrained('pembayaran')->onDelete('cascade');
            $table->string('no_kwitansi')->nullable();
            $table->enum('jenis_transaksi', ['dp', 'cicilan', 'pelunasan']);
            $table->bigInteger('jumlah_bayar')->default(0);
            $table->date('tanggal_bayar');
            $table->enum('metode_pembayaran', ['tunai', 'transfer']);
            $table->enum('tempat_pembayaran', ['Tubanan', 'Nginden']);
            $table->string('catatan')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pembayaran');
    }
};