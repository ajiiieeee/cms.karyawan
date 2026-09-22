<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'email',
        'nama',
        'foto',
        'grup_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // ==================== Relationships ====================

    public function grup(): BelongsTo
    {
        return $this->belongsTo(Grup::class, 'grup_id');
    }

    // ==================== Menu Access Helpers ====================

    /**
     * Check if user's grup has access to a specific menu link with a specific action.
     * $action: 'view', 'add', 'edit', 'delete'
     */
    public function hasMenuAccess(string $menuLink, string $action = 'view'): bool
    {
        if (!in_array($action, ['view', 'add', 'edit', 'delete'])) {
            return false;
        }

        $accessMap = Cache::remember("menu_access:grup:{$this->grup_id}", 3600, function () {
            return MenuAkses::where('grup_id', $this->grup_id)
                ->with('menu')
                ->get()
                ->filter(fn($akses) => $akses->menu !== null && $akses->menu->link)
                ->keyBy(fn($akses) => $akses->menu->link)
                ->map(fn($akses) => [
                    'view'   => (bool) $akses->view,
                    'add'    => (bool) $akses->add,
                    'edit'   => (bool) $akses->edit,
                    'delete' => (bool) $akses->delete,
                ])
                ->toArray();
        });

        return ($accessMap[$menuLink][$action] ?? false) === true;
    }

    /**
     * Get all menu access entries for this user's grup.
     */
    public function getMenuAccess(): \Illuminate\Database\Eloquent\Collection
    {
        return MenuAkses::with('menu')
            ->where('grup_id', $this->grup_id)
            ->where('view', 1)
            ->get();
    }
}
