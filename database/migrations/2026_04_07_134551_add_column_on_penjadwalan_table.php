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
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_pendaftaran_id')->nullable()->after('karyawan_id');
            $table->unsignedBigInteger('bidang_studi_id')->nullable()->after('detail_pendaftaran_id');
            $table->unsignedBigInteger('level_kelas_id')->nullable()->after('bidang_studi_id');

            $table->foreign('detail_pendaftaran_id')->references('id')->on('detail_pendaftaran')->onDelete('set null');
            $table->foreign('bidang_studi_id')->references('id')->on('bidang_studis')->onDelete('set null');
            $table->foreign('level_kelas_id')->references('id')->on('level_kelas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->dropForeign(['detail_pendaftaran_id']);
            $table->dropForeign(['bidang_studi_id']);
            $table->dropForeign(['level_kelas_id']);
            $table->dropColumn(['detail_pendaftaran_id', 'bidang_studi_id', 'level_kelas_id']);
        });
    }
};
