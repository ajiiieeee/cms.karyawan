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
        Schema::table('bidang_studis', function (Blueprint $table) {
            $table->unsignedBigInteger('level_kelas_id')->after('nama_bidang_studi')->nullable();
            $table->unsignedBigInteger('kategori_kelas_id')->after('level_kelas_id')->nullable();
            $table->bigInteger('harga_kursus')->after('kategori_kelas_id')->nullable()->default(0);

            $table->foreign('level_kelas_id')->references('id')->on('level_kelas')->onDelete('cascade');
            $table->foreign('kategori_kelas_id')->references('id')->on('kategori_kelas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bidang_studis', function (Blueprint $table) {
            $table->dropForeign(['level_kelas_id']);
            $table->dropForeign(['kategori_kelas_id']);
            $table->dropColumn(['level_kelas_id', 'kategori_kelas_id', 'harga_kursus']);
        });
    }
};
