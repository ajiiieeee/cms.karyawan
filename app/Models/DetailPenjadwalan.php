<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjadwalan extends Model
{
    protected $table = 'detail_penjadwalan';

    protected $fillable = [
        'penjadwalan_id',
        'hari',
        'jam_mulai',
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai' => 'string',
        ];
    }

    public function penjadwalan()
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id');
    }
}
