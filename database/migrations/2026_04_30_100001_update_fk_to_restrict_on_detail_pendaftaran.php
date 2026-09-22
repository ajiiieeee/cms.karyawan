<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['detail_pendaftaran_id']);
            $table->foreign('detail_pendaftaran_id')->references('id')->on('detail_pendaftaran')->onDelete('restrict');
        });

        Schema::table('penjadwalan', function (Blueprint $table) {
            if (Schema::hasColumn('penjadwalan', 'detail_pendaftaran_id')) {
                $table->dropForeign(['detail_pendaftaran_id']);
                $table->foreign('detail_pendaftaran_id')->references('id')->on('detail_pendaftaran')->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropForeign(['detail_pendaftaran_id']);
            $table->foreign('detail_pendaftaran_id')->references('id')->on('detail_pendaftaran')->onDelete('cascade');
        });

        Schema::table('penjadwalan', function (Blueprint $table) {
            if (Schema::hasColumn('penjadwalan', 'detail_pendaftaran_id')) {
                $table->dropForeign(['detail_pendaftaran_id']);
                $table->foreign('detail_pendaftaran_id')->references('id')->on('detail_pendaftaran')->onDelete('set null');
            }
        });
    }
};