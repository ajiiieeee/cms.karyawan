<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaryawanMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder adds the Karyawan menu under Data Master parent,
     * positioned above Bidang Studi.
     */
    public function run(): void
    {
        $now = now();

        // Find the Data Master parent menu
        $dataMasterMenu = DB::table('tbl_menu')
            ->where('nama_menu', 'Data Master')
            ->first();

        if (!$dataMasterMenu) {
            $this->command->error('Data Master menu not found. Please create it first.');
            return;
        }

        // Check if Karyawan menu already exists
        $existingMenu = DB::table('tbl_menu')
            ->where('nama_menu', 'Karyawan')
            ->where('parent', $dataMasterMenu->id_menu)
            ->first();

        if ($existingMenu) {
            $this->command->info('Karyawan menu already exists. Skipping...');
            return;
        }

        // Find Bidang Studi menu to determine ordering (Karyawan should be above Bidang Studi)
        $bidangStudiMenu = DB::table('tbl_menu')
            ->where('nama_menu', 'Bidang Studi')
            ->where('parent', $dataMasterMenu->id_menu)
            ->first();

        // Set urutan to 1 (or before Bidang Studi)
        $urutan = 1;
        
        if ($bidangStudiMenu) {
            // Increment urutan for all existing submenus to make room for Karyawan at position 1
            DB::table('tbl_menu')
                ->where('parent', $dataMasterMenu->id_menu)
                ->where('urutan', '>=', $urutan)
                ->increment('urutan');
        }

        // Insert Karyawan menu
        $menuId = DB::table('tbl_menu')->insertGetId([
            'nama_menu'         => 'Karyawan',
            'link'              => '/data-master/karyawan',
            'icon'              => 'ri-team-line',
            'parent'            => $dataMasterMenu->id_menu,
            'kode_menu'         => 'KARYAWAN',
            'menu_file'         => 'karyawan',
            'urutan'            => $urutan,
            'last_created_date' => $now,
            'last_update_date'  => $now,
            'created_by'        => 'system',
            'last_update_by'    => 'system',
        ]);

        $this->command->info("Karyawan menu created with ID: {$menuId}");

        // Give access to all existing groups
        $groups = DB::table('grup')->get();

        foreach ($groups as $group) {
            DB::table('menu_akses')->insert([
                'id_menu' => $menuId,
                'grup_id' => $group->grup_id,
                'view'    => 1,
                'add'     => 1,
                'edit'    => 1,
                'del'     => 1,
            ]);
        }

        $this->command->info('Menu access added for all groups.');
    }
}
