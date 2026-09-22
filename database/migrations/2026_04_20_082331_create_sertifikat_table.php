<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikat', function (Blueprint $table) {
            $table->id();
            $table->string('no_sertifikat');
            $table->date('tanggal');
            $table->string('nama_siswa');
            $table->string('nama_bidang_studi');
            $table->string('level');
            $table->date('tgl_mulai');
            $table->date('tgl_selesai');
            $table->unsignedBigInteger('penjadwalan_id');
            $table->foreign('penjadwalan_id')->references('id')->on('penjadwalan')->onDelete('restrict');
            $table->string('signature_by')->nullable();
            $table->datetime('signature_date')->nullable();
            $table->enum('status', ['pending', 'selesai'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat');
    }
};
