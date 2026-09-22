<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjadwalan extends Model
{
    use HasFactory;

    protected $table = 'penjadwalan';

    protected $fillable = [
        'pembayaran_id',
        'siswa_id',
        'karyawan_id',
        'detail_pendaftaran_id',
        'bidang_studi_id',
        'level_kelas_id',
        'lokasi',
        'jumlah_pertemuan',
        'tgl_mulai',
        'tgl_selesai',
        'nama_gbmp',
        'upload_gbmp',
        'status_jadwal',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai'         => 'date',
            'tgl_selesai'       => 'date',
            'jumlah_pertemuan'  => 'integer',
        ];
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function bidangStudi()
    {
        return $this->belongsTo(BidangStudi::class, 'bidang_studi_id');
    }

    public function levelKelas()
    {
        return $this->belongsTo(LevelKelas::class, 'level_kelas_id');
    }

    public function detailPendaftaran()
    {
        return $this->belongsTo(DetailPendaftaran::class, 'detail_pendaftaran_id');
    }

    public function detailPenjadwalan()
    {
        return $this->hasMany(DetailPenjadwalan::class, 'penjadwalan_id');
    }

    public function sertifikat()
    {
        return $this->hasOne(Sertifikat::class, 'penjadwalan_id');
    }
}
