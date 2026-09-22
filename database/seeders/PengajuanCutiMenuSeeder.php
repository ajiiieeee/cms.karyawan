<?php

namespace Database\Seeders;

use App\Models\Grup;
use App\Models\Menu;
use App\Models\MenuAkses;
use Illuminate\Database\Seeder;

class PengajuanCutiMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Create Pengajuan Cuti menu as main menu (above Data Master)
        $menu = Menu::updateOrCreate(
            ['nama_menu' => 'Pengajuan Cuti'],
            [
                'link'   => 'pengajuan-cuti',
                'icon'   => 'ri-calendar-check-line',
                'parent' => 0,
                'urutan' => 1,
            ]
        );

        // Update Data Master urutan to 2
        Menu::where('nama_menu', 'Data Master')->update(['urutan' => 2]);

        // Update Settings urutan to 3
        Menu::where('nama_menu', 'Settings')->update(['urutan' => 3]);

        // Give Super Admin full access to Pengajuan Cuti
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
