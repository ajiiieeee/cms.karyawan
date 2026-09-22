<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'nama_menu',
        'link',
        'icon',
        'parent',
        'urutan',
    ];

    public function parentMenu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent')->orderBy('urutan');
    }

    public function menuAkses(): HasMany
    {
        return $this->hasMany(MenuAkses::class, 'menu_id');
    }
}
