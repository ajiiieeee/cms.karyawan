<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'nama_siswa',
        'nis',
        'nik',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'agama_lainnya',
        'email',
        'no_telepon',
        'alamat',
        'jenis_tinggal',
        'jenis_tinggal_lainnya',
        'kota',
        'provinsi',
        'pendidikan_terakhir',
        'pendidikan_terakhir_lainnya',
        'abk',
        'abk_lainnya',
        'pekerjaan',
        'pekerjaan_lainnya',
        'foto',
        'status_siswa',
        'upload_ktp',
        'upload_kk',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'status_siswa' => 'boolean',
        ];
    }

    public function pendaftaran()
    {
        return $this->hasMany(Pendaftaran::class, 'siswa_id', 'id');
    }

    public function penjadwalan()
    {
        return $this->hasMany(Penjadwalan::class, 'siswa_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id');
    }

    public function detailPendaftaran()
    {
        return $this->hasOne(DetailPendaftaran::class, 'siswa_id');
    }
}
