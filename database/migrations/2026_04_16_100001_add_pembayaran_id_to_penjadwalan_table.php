<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->unsignedBigInteger('pembayaran_id')->nullable()->after('id');

            $table->foreign('pembayaran_id')
                  ->references('id')
                  ->on('pembayaran')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('penjadwalan', function (Blueprint $table) {
            $table->dropForeign(['pembayaran_id']);
            $table->dropColumn('pembayaran_id');
        });
    }
};
