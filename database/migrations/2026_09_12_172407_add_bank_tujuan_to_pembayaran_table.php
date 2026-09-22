<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Kita taruh setelah jenis_pembayaran dan set nullable (karena jika tunai, nilainya null)
            $table->string('bank_tujuan', 50)->nullable()->after('jenis_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('bank_tujuan');
        });
    }
};
