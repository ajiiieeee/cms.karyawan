<?php

namespace Database\Seeders;

use App\Models\Grup;
use App\Models\Menu;
use App\Models\MenuAkses;
use Illuminate\Database\Seeder;

class GrupMenuAksesSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin grup
        $superAdmin = Grup::updateOrCreate(
            ['nama_grup' => 'Super Admin'],
            ['deskripsi' => 'Grup dengan akses penuh ke semua menu']
        );

        // Create Staff grup
        Grup::updateOrCreate(
            ['nama_grup' => 'Staff'],
            ['deskripsi' => 'Grup dengan akses terbatas']
        );

        // Give Super Admin full access to all menus
        $menus = Menu::all();
        foreach ($menus as $menu) {
            MenuAkses::updateOrCreate(
                [
                    'menu_id' => $menu->id,
                    'grup_id' => $superAdmin->id,
                ],
                [
                    'view'   => 1,
                    'add'    => 1,
                    'edit'   => 1,
                    'delete' => 1,
                ]
            );
        }
    }
}
