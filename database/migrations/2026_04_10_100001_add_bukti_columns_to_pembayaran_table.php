<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('bukti_uang_muka')->nullable()->after('no_kwitansi_pelunasan');
            $table->string('bukti_pelunasan')->nullable()->after('bukti_uang_muka');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn(['bukti_uang_muka', 'bukti_pelunasan']);
        });
    }
};
