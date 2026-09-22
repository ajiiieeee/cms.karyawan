<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuAkses extends Model
{
    protected $table = 'menu_akses';

    public $timestamps = false;

    protected $fillable = [
        'menu_id',
        'grup_id',
        'view',
        'add',
        'edit',
        'delete',
    ];

    protected function casts(): array
    {
        return [
            'view'   => 'integer',
            'add'    => 'integer',
            'edit'   => 'integer',
            'delete' => 'integer',
        ];
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function grup(): BelongsTo
    {
        return $this->belongsTo(Grup::class, 'grup_id');
    }
}
