<?php

namespace Database\Seeders;

use App\Models\Grup;
use App\Models\Menu;
use App\Models\MenuAkses;
use Illuminate\Database\Seeder;

class FimpMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Get current Pengajuan Cuti urutan
        $cutiMenu = Menu::where('nama_menu', 'Pengajuan Cuti')->first();
        $cutiUrutan = $cutiMenu ? $cutiMenu->urutan : 1;

        // Create Pengajuan FIMP menu right after Pengajuan Cuti
        $menu = Menu::updateOrCreate(
            ['nama_menu' => 'Pengajuan FIMP'],
            [
                'link'   => 'pengajuan-fimp',
                'icon'   => 'ri-file-list-3-line',
                'parent' => 0,
                'urutan' => $cutiUrutan + 1,
            ]
        );

        // Push Data Master and Settings urutan down
        Menu::where('nama_menu', 'Data Master')->update(['urutan' => $cutiUrutan + 2]);
        Menu::where('nama_menu', 'Settings')->update(['urutan' => $cutiUrutan + 3]);

        // Give Super Admin full access to Pengajuan FIMP
        $superAdmin = Grup::where('nama_grup', 'Super Admin')->first();
        if ($superAdmin) {
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
