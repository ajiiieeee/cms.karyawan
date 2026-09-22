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
        Schema::table('detail_pendaftaran', function (Blueprint $table) {
            $table->enum('tempat_daftar', ['Tubanan', 'Nginden'])->nullable()->after('kategori_kelas_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_pendaftaran', function (Blueprint $table) {
            $table->dropColumn('tempat_daftar');
        });
    }
};
