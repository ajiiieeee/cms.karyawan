<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKelas extends Model
{
    protected $table = 'kategori_kelas';

    protected $fillable = [
        'nama_kategori',
    ];
}
