<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grup extends Model
{
    protected $table = 'grup';

    protected $fillable = [
        'nama_grup',
        'deskripsi',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'grup_id');
    }

    public function menuAkses(): HasMany
    {
        return $this->hasMany(MenuAkses::class, 'grup_id');
    }
}
