<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LevelKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\LevelKelas::insert([
            ['nama_level' => 'Basic'],
            ['nama_level' => 'Advanced'],
            ['nama_level' => 'Custom'],
        ]);
    }
}
