<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelKelas extends Model
{
    protected $table = 'level_kelas';

    protected $fillable = [
        'nama_level',
    ];
}
