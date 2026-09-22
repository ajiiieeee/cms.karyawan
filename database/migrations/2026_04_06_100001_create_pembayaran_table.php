<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detail_pendaftaran_id');
            $table->foreign('detail_pendaftaran_id')->references('id')->on('detail_pendaftaran')->onDelete('restrict');
            $table->unsignedBigInteger('pendaftaran_id');
            $table->foreign('pendaftaran_id')->references('id')->on('pendaftaran')->onDelete('restrict');
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('restrict');
            $table->bigInteger('jumlah_tagihan')->default(0);
            $table->date('tanggal_pembayaran');
            $table->enum('jenis_pembayaran', ['tunai', 'transfer']);
            $table->bigInteger('uang_muka')->default(0);
            $table->bigInteger('pelunasan')->nullable();
            $table->enum('tempat_pembayaran', ['Tubanan', 'Nginden']);
            $table->bigInteger('sisa_tagihan')->default(0);
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
