<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fimp extends Model
{
    protected $table = 'fimp';

    protected $fillable = [
        'karyawan_id',
        'tanggal_awal',
        'tanggal_akhir',
        'total_hari',
        'karyawan_pengganti',
        'keperluan',
        'reject_statement',
        'pengganti_mengetahui',
        'pengganti_bersedia',
        'status_pengajuan',
        'status_approval',
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
            'status_pengajuan' => 'integer',
            'total_hari' => 'integer',
            'pengganti_mengetahui' => 'boolean',
            'pengganti_bersedia' => 'boolean',
        ];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function penggantiKaryawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_pengganti');
    }
}
