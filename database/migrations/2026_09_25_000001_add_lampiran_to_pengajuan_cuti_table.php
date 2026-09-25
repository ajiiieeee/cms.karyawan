<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengajuan_cuti') && !Schema::hasColumn('pengajuan_cuti', 'lampiran')) {
            Schema::table('pengajuan_cuti', function (Blueprint $table) {
                $table->string('lampiran', 255)->nullable()->after('keterangan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pengajuan_cuti') && Schema::hasColumn('pengajuan_cuti', 'lampiran')) {
            Schema::table('pengajuan_cuti', function (Blueprint $table) {
                $table->dropColumn('lampiran');
            });
        }
    }
};
