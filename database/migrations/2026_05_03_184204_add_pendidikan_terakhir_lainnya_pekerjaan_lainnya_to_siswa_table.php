<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('pendidikan_terakhir_lainnya')->nullable()->after('pendidikan_terakhir');
            $table->string('pekerjaan_lainnya')->nullable()->after('pekerjaan');
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn(['pendidikan_terakhir_lainnya', 'pekerjaan_lainnya']);
        });
    }
};
