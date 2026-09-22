<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriCuti;

class KategoriCutiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI TAHUNAN (ANNUAL LEAVE)'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI KAWIN (MARITAL LEAVE) - 3 DAYS'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI HAMIL (MATERNITY LEAVE) - 3 MONTHS'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI ISTRI MELAHIRKAN (BABY BORN) - 2 DAYS'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI ANAK KAWIN ATAU BAPTIS (WEDDING OR BAPTIS OF)'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI KEMATIAN KELUARGA (FAMILY MEMBER PASSED AWAY)'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI MASAL (MASS LEAVE)'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI KEMATIAN KAKAK/ADIK KANDUNG ATAU IPAR'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI DATANG BULAN (MENSTRUATION LEAVE) - 2 DAYS'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI PERNIKAHAN KAKAK/ADIK (WEDDING OF BROTHER/SISTER)'],
        );
        KategoriCuti::updateOrCreate(
            ['nama_kategori' => 'CUTI KHITANAN, TASMIYAH/AQIQAH ANAK (CIRCUMCISION, TASMIYAH/AQIQAH CHILD)'],
        );
    }
}
