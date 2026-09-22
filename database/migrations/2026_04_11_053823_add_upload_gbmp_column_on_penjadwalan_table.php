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
            $table->string('nama_gbmp')->nullable()->after('tgl_selesai');
            $table->string('upload_gbmp')->nullable()->after('nama_gbmp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->dropColumn('upload_gbmp');
            $table->dropColumn('nama_gbmp');
        });
    }
};
