<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawans';

    protected $fillable = [
        'nama_karyawan',
        'nik',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'email',
        'alamat',
        'kota',
        'provinsi',
        'telefon',
        'telefon_alternatif',
        'pendidikan_terakhir',
        'lembaga_pendidikan',
        'tahun_lulus',
        'jabatan',
        'tanggal_masuk',
        'foto',
        'status_akun',
        'nama_keluarga',
        'alamat_keluarga',
        'pendidikan_keluarga',
        'hubungan_keluarga',
        'telefon_keluarga',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'tanggal_lahir' => 'date',
            'tanggal_masuk' => 'date',
            'tahun_lulus' => 'integer',
        ];
    }

    public function saldoCuti(): HasOne
    {
        return $this->hasOne(SaldoCuti::class, 'karyawan_id')->latest('id');
    }

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(PengajuanCuti::class, 'karyawan_id');
    }

    public function fimp(): HasMany
    {
        return $this->hasMany(Fimp::class, 'karyawan_id');
    }
}
