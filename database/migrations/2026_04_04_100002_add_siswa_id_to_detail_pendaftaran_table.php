<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pendaftaran', function (Blueprint $table) {
            $table->unsignedBigInteger('siswa_id')->nullable()->after('pendaftaran_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('detail_pendaftaran', function (Blueprint $table) {
            $table->dropForeign(['siswa_id']);
            $table->dropColumn('siswa_id');
        });
    }
};
