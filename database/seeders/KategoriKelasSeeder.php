<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategoriKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\KategoriKelas::insert([
            ['nama_kategori' => 'Reguler Class'],
            ['nama_kategori' => 'Inspiration Class'],
            ['nama_kategori' => 'Business Class'],
            ['nama_kategori' => 'Executive Class'],
        ]);
    }
}
