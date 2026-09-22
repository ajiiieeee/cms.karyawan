<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BidangStudi;

class BidangStudiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Operator Komputer',
                'deskripsi' => ''
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Komputer Akuntansi',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Desain Grafis',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Desain Interior',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Desain Arsitektur',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Editing Video Multimedia',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Pemrograman Web',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Website Desain CMS',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Pemrograman Java Android',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Web Desainer',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Animasi',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Pemrograman Dasar',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Fotografi',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Multimedia',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Desain Komunikasi Visual',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Content Creator',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Power BI',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Pemrograman Flutter',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Videografi',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Junior Web Programmer',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Microsoft Excel',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Public Speaking',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Animasi 3D',
            ]
        );
        BidangStudi::updateOrCreate(
            [
                'nama_bidang_studi' => 'Lainnya',
            ]
        );
    }
}
