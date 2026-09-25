<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanCuti extends Model
{
    protected $table = 'pengajuan_cuti';

    protected $fillable = [
        'karyawan_id',
        'tanggal_awal',
        'tanggal_akhir',
        'jenis_cuti',
        'jumlah_hari',
        'status_approval',
        'keterangan',
        'lampiran',
        'reject_statement',
        'status_pengajuan',
        'created_date',
        'updated_date',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_awal' => 'date',
            'tanggal_akhir' => 'date',
            'created_date' => 'date',
            'updated_date' => 'date',
            'status_approval' => 'integer',
        ];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function kategoriCuti(): BelongsTo
    {
        return $this->belongsTo(KategoriCuti::class, 'jenis_cuti');
    }
}
