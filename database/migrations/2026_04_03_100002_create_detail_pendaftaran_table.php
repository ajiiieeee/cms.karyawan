<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pendaftaran_id');
            $table->foreign('pendaftaran_id')->references('id')->on('pendaftaran')->onDelete('cascade');
            $table->unsignedBigInteger('bidang_studi_id');
            $table->foreign('bidang_studi_id')->references('id')->on('bidang_studis')->onDelete('restrict');
            $table->string('bidang_studi_custom')->nullable();
            $table->unsignedBigInteger('level_kelas_id');
            $table->foreign('level_kelas_id')->references('id')->on('level_kelas')->onDelete('restrict');
            $table->unsignedBigInteger('kategori_kelas_id');
            $table->foreign('kategori_kelas_id')->references('id')->on('kategori_kelas')->onDelete('restrict');
            $table->bigInteger('harga_kursus')->default(0);
            $table->integer('diskon1')->nullable();
            $table->integer('diskon2')->nullable();
            $table->bigInteger('total_harga')->default(0);
            $table->timestamps();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pendaftaran');
    }
};
