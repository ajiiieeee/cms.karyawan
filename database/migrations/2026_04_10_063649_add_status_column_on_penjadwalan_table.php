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
            $table->integer('status_jadwal')->default(0)->after('tgl_selesai')->comment('0: Berjalan, 1: Selesai, 2: DO');
            $table->text('keterangan')->nullable()->after('status_jadwal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->dropColumn('status_jadwal');
            $table->dropColumn('keterangan');
        });
    }
};
