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
        $pembayarans = \App\Models\Pembayaran::whereDoesntHave('riwayat')->get();

        foreach ($pembayarans as $p) {
            if ($p->uang_muka && $p->uang_muka > 0) {
                \App\Models\RiwayatPembayaran::create([
                    'pembayaran_id'     => $p->id,
                    'no_kwitansi'       => $p->no_kwitansi_dp ?? '-',
                    'jenis_transaksi'   => 'dp',
                    'jumlah_bayar'      => $p->uang_muka,
                    'tanggal_bayar'     => $p->tanggal_pembayaran ? $p->tanggal_pembayaran->toDateString() : now()->toDateString(),
                    'metode_pembayaran' => $p->jenis_pembayaran ?? 'tunai',
                    'tempat_pembayaran' => $p->tempat_pembayaran ?? 'Tubanan',
                    'catatan'           => 'Pembayaran uang muka (Migrasi Data Lama)',
                    'created_by'        => $p->created_by ?? 'System',
                ]);
            }

            if ($p->pelunasan && $p->pelunasan > 0) {
                \App\Models\RiwayatPembayaran::create([
                    'pembayaran_id'     => $p->id,
                    'no_kwitansi'       => $p->no_kwitansi_pelunasan ?? '-',
                    'jenis_transaksi'   => 'pelunasan',
                    'jumlah_bayar'      => $p->pelunasan,
                    'tanggal_bayar'     => $p->tanggal_pelunasan ? $p->tanggal_pelunasan->toDateString() : ($p->tanggal_pembayaran ? $p->tanggal_pembayaran->toDateString() : now()->toDateString()),
                    'metode_pembayaran' => $p->jenis_pembayaran ?? 'tunai',
                    'tempat_pembayaran' => $p->tempat_pembayaran ?? 'Tubanan',
                    'catatan'           => 'Pelunasan pembayaran (Migrasi Data Lama)',
                    'created_by'        => $p->created_by ?? 'System',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus hanya data yang dibuat oleh script migrasi ini
        \App\Models\RiwayatPembayaran::where('catatan', 'like', '%(Migrasi Data Lama)%')->delete();
    }
};
