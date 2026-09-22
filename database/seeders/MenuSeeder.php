<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Parent menu: Pendaftaran (main menu)
        Menu::updateOrCreate(
            ['nama_menu' => 'Pendaftaran'],
            [
                'link'   => 'pendaftaran',
                'icon'   => 'ri-file-list-3-line',
                'parent' => 0,
                'urutan' => 1,
            ]
        );

        // Parent menu: Pembayaran (main menu, below Pendaftaran)
        Menu::updateOrCreate(
            ['nama_menu' => 'Pembayaran'],
            [
                'link'   => 'pembayaran',
                'icon'   => 'ri-money-dollar-circle-line',
                'parent' => 0,
                'urutan' => 2,
            ]
        );

        // Parent menu: Penjadwalan (main menu, below Pembayaran)
        Menu::updateOrCreate(
            ['nama_menu' => 'Penjadwalan'],
            [
                'link'   => 'penjadwalan',
                'icon'   => 'ri-calendar-schedule-line',
                'parent' => 0,
                'urutan' => 3,
            ]
        );

        // Parent menu: Pelatihan (main menu, above Pengajuan Cuti)
        Menu::updateOrCreate(
            ['nama_menu' => 'Pelatihan'],
            [
                'link'   => 'pelatihan',
                'icon'   => 'ri-presentation-line',
                'parent' => 0,
                'urutan' => 4,
            ]
        );

        // Parent menu: Pengajuan Cuti (main menu, above Data Master)
        Menu::updateOrCreate(
            ['nama_menu' => 'Pengajuan Cuti'],
            [
                'link'   => 'pengajuan-cuti',
                'icon'   => 'ri-calendar-check-line',
                'parent' => 0,
                'urutan' => 5,
            ]
        );

        // Parent menu: Saldo Cuti (main menu, below Pengajuan Cuti)
        Menu::updateOrCreate(
            ['nama_menu' => 'Saldo Cuti'],
            [
                'link'   => 'saldo-cuti',
                'icon'   => 'ri-calendar-2-line',
                'parent' => 0,
                'urutan' => 6,
            ]
        );

        // Parent menu: List Sertifikat (main menu, below Saldo Cuti)
        Menu::updateOrCreate(
            ['nama_menu' => 'List Sertifikat'],
            [
                'link'   => 'sertifikat',
                'icon'   => 'ri-award-line',
                'parent' => 0,
                'urutan' => 7,
            ]
        );

        // Parent menu: Data Master
        $dataMaster = Menu::updateOrCreate(
            ['nama_menu' => 'Data Master'],
            [
                'link'   => null,
                'icon'   => 'ri-database-2-line',
                'parent' => 0,
                'urutan' => 8,
            ]
        );

        // Child menu under Data Master: Siswa (above Karyawan)
        Menu::updateOrCreate(
            ['nama_menu' => 'Siswa'],
            [
                'link'   => 'siswa',
                'icon'   => null,
                'parent' => $dataMaster->id,
                'urutan' => 1,
            ]
        );

        // Child menu under Data Master: Karyawan
        Menu::updateOrCreate(
            ['nama_menu' => 'Karyawan'],
            [
                'link'   => 'karyawan',
                'icon'   => null,
                'parent' => $dataMaster->id,
                'urutan' => 2,
            ]
        );

        // Parent menu: Settings
        $settings = Menu::updateOrCreate(
            ['nama_menu' => 'Settings'],
            [
                'link'   => null,
                'icon'   => 'ri-settings-3-line',
                'parent' => 0,
                'urutan' => 9,
            ]
        );

        // Child menus under Settings
        Menu::updateOrCreate(
            ['nama_menu' => 'User Management'],
            [
                'link'   => 'user-management',
                'icon'   => null,
                'parent' => $settings->id,
                'urutan' => 1,
            ]
        );

        Menu::updateOrCreate(
            ['nama_menu' => 'Grup Management'],
            [
                'link'   => 'grup-management',
                'icon'   => null,
                'parent' => $settings->id,
                'urutan' => 2,
            ]
        );

        Menu::updateOrCreate(
            ['nama_menu' => 'Menu Management'],
            [
                'link'   => 'menu-management',
                'icon'   => null,
                'parent' => $settings->id,
                'urutan' => 3,
            ]
        );
    }
}
