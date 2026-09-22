<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelatihan extends Model
{
    use HasFactory;

    protected $table = 'pelatihan';

    protected $fillable = [
        'tanggal_pendaftaran',
        'nama_perusahaan',
        'alamat',
        'kota',
        'provinsi',
        'nama_pic',
        'telepon',
        'email',
        'tempat_daftar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pendaftaran' => 'date',
        ];
    }
}
