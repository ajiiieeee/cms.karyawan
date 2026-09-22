<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaldoCuti extends Model
{
    protected $table = 'saldo_cuti';

    protected $fillable = [
        'karyawan_id',
        'total_cuti',
        'periode_mulai',
        'periode_selesai',
    ];

    protected function casts(): array
    {
        return [
            'periode_mulai'   => 'date',
            'periode_selesai' => 'date',
            'total_cuti'      => 'integer',
        ];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Hitung jumlah hari cuti yang sudah terpakai oleh karyawan
     * berdasarkan pengajuan cuti dengan jenis CUTI TAHUNAN yang di-approve
     * di dalam periode ini.
     */
    public function getSaldoTerpakaiAttribute(): int
    {
        return PengajuanCuti::join('kategori_cuti', 'pengajuan_cuti.jenis_cuti', '=', 'kategori_cuti.id')
            ->where('pengajuan_cuti.karyawan_id', $this->karyawan_id)
            ->where('kategori_cuti.nama_kategori', 'LIKE', '%tahunan%')
            ->where('pengajuan_cuti.status_pengajuan', 'Approve')
            ->whereBetween('pengajuan_cuti.tanggal_awal', [
                $this->periode_mulai,
                $this->periode_selesai,
            ])
            ->sum('pengajuan_cuti.jumlah_hari');
    }

    /**
     * Sisa saldo cuti yang masih tersedia.
     */
    public function getSaldoSisaAttribute(): int
    {
        return max(0, $this->total_cuti - $this->saldo_terpakai);
    }
}
