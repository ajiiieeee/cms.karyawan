<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->date('tanggal_pelunasan')->nullable()->after('tanggal_pembayaran');
            $table->string('no_kwitansi_dp')->nullable()->unique()->after('status_pembayaran');
            $table->string('no_kwitansi_pelunasan')->nullable()->unique()->after('no_kwitansi_dp');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pelunasan', 'no_kwitansi_dp', 'no_kwitansi_pelunasan']);
        });
    }
};
