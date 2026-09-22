<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriCuti extends Model
{
    protected $table = 'kategori_cuti';

    protected $fillable = [
        'nama_kategori',
    ];
}
