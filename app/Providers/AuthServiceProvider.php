<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $this->registerMenuGates();
    }

    /**
     * Register dynamic gates based on menu_akses table.
     * Gate names: menu-view.{link}, menu-add.{link}, menu-edit.{link}, menu-delete.{link}
     */
    private function registerMenuGates(): void
    {
        // Define gates for each action type
        $actions = ['view', 'add', 'edit', 'del'];

        foreach ($actions as $action) {
            Gate::define("menu-{$action}", function ($user, $menuLink = null) use ($action) {
                if (!$menuLink) {
                    return false;
                }

                $grupId = $user->grup_id ?? null;
                if (!$grupId) {
                    return false;
                }

                try {
                    // Find menu by link
                    $menu = DB::table('menu')
                        ->where('link', $menuLink)
                        ->first();

                    if (!$menu) {
                        return false;
                    }

                    // Check access
                    $access = DB::table('menu_akses')
                        ->where('menu_id', $menu->id)
                        ->where('grup_id', $grupId)
                        ->first();

                    if (!$access) {
                        return false;
                    }

                    return (int) ($access->{$action} ?? 0) === 1;
                } catch (\Exception $e) {
                    return false;
                }
            });
        }
    }
}
