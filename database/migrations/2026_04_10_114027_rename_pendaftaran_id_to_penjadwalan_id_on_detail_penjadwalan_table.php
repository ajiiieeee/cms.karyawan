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
        Schema::table('detail_penjadwalan', function (Blueprint $table) {
            $table->renameColumn('pendaftaran_id', 'penjadwalan_id');
        });

        Schema::table('detail_penjadwalan', function (Blueprint $table) {
            $table->foreign('penjadwalan_id')->references('id')->on('penjadwalan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_penjadwalan', function (Blueprint $table) {
            $table->dropForeign(['penjadwalan_id']);
        });

        Schema::table('detail_penjadwalan', function (Blueprint $table) {
            $table->renameColumn('penjadwalan_id', 'pendaftaran_id');
        });
    }
};
