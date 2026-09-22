<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainerMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder adds the Trainer menu under Data Master parent,
     * positioned below Bidang Studi.
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

        // Find Bidang Studi menu to determine ordering
        $bidangStudiMenu = DB::table('tbl_menu')
            ->where('nama_menu', 'Bidang Studi')
            ->where('parent', $dataMasterMenu->id_menu)
            ->first();

        $urutan = $bidangStudiMenu ? ($bidangStudiMenu->urutan + 1) : 2;

        // Check if Trainer menu already exists
        $existingMenu = DB::table('tbl_menu')
            ->where('nama_menu', 'Trainer')
            ->where('parent', $dataMasterMenu->id_menu)
            ->first();

        if ($existingMenu) {
            $this->command->info('Trainer menu already exists. Skipping...');
            return;
        }

        // Insert Trainer menu
        $menuId = DB::table('tbl_menu')->insertGetId([
            'nama_menu'         => 'Trainer',
            'link'              => '/data-master/trainer',
            'icon'              => 'ri-user-star-line',
            'parent'            => $dataMasterMenu->id_menu,
            'kode_menu'         => 'TRAINER',
            'menu_file'         => 'trainer',
            'urutan'            => $urutan,
            'last_created_date' => $now,
            'last_update_date'  => $now,
            'created_by'        => 'system',
            'last_update_by'    => 'system',
        ]);

        $this->command->info("Trainer menu created with ID: {$menuId}");

        // Give access to all existing groups (optional)
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
