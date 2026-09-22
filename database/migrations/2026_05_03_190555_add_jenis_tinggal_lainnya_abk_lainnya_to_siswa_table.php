<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('siswa', 'jenis_tinggal_lainnya')) {
                $table->string('jenis_tinggal_lainnya')->nullable()->after('jenis_tinggal');
            }
            if (!Schema::hasColumn('siswa', 'abk_lainnya')) {
                $table->string('abk_lainnya')->nullable()->after('abk');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'jenis_tinggal_lainnya')) {
                $table->dropColumn('jenis_tinggal_lainnya');
            }
            if (Schema::hasColumn('siswa', 'abk_lainnya')) {
                $table->dropColumn('abk_lainnya');
            }
        });
    }
};
