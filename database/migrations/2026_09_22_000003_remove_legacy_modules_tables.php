<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $legacyTables = [
        'sertifikat_bulk_sign_logs', 'riwayat_pembayaran', 'detail_penjadwalan',
        'sertifikat', 'penjadwalan', 'pembayaran', 'detail_pendaftaran',
        'pendaftaran', 'siswa', 'pelatihan', 'bidang_studis', 'kategori_kelas',
        'level_kelas', 'saldo_cuti', 'menu_akses', 'menu', 'grup', 'jobs', 'failed_jobs',
    ];

    public function up(): void
    {
        // Existing installations still have this old role/menu foreign key.
        if (Schema::hasColumn('users', 'grup_id')) {
            $foreignKeys = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [DB::getDatabaseName(), 'users', 'grup_id']
            );

            foreach ($foreignKeys as $key) {
                DB::statement('ALTER TABLE `users` DROP FOREIGN KEY `' . str_replace('`', '``', $key->CONSTRAINT_NAME) . '`');
            }

            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('grup_id'));
        }

        foreach ($this->legacyTables as $table) {
            Schema::dropIfExists($table);
        }
    }

    public function down(): void
    {
        // Data lama sengaja tidak dibuat ulang; pulihkan dari backup database bila diperlukan.
    }
};
