<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPendaftaran extends Model
{
    use HasFactory;

    protected $table = 'detail_pendaftaran';

    protected $fillable = [
        'pendaftaran_id',
        'no_pendaftaran',
        'siswa_id',
        'bidang_studi_id',
        'bidang_studi_custom',
        'level_kelas_id',
        'kategori_kelas_id',
        'tempat_daftar',
        'harga_kursus',
        'diskon1',
        'diskon2',
        'total_harga',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'harga_kursus' => 'integer',
            'diskon1'      => 'integer',
            'diskon2'      => 'integer',
            'total_harga'  => 'integer',
        ];
    }

    public function pendaftaran()
    {
        return $this->belongsTo(Pendaftaran::class, 'pendaftaran_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function bidangStudi()
    {
        return $this->belongsTo(BidangStudi::class, 'bidang_studi_id');
    }

    public function levelKelas()
    {
        return $this->belongsTo(LevelKelas::class, 'level_kelas_id');
    }

    public function kategoriKelas()
    {
        return $this->belongsTo(KategoriKelas::class, 'kategori_kelas_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'detail_pendaftaran_id');
    }

    public function penjadwalan()
    {
        return $this->hasOne(Penjadwalan::class, 'detail_pendaftaran_id');
    }
}
