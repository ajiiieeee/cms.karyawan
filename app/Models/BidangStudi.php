<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidangStudi extends Model
{
    protected $table = 'bidang_studis';

    protected $fillable = [
        'nama_bidang_studi',
        'deskripsi'
    ];
}
