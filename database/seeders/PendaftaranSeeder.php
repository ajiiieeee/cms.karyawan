<?php

namespace Database\Seeders;

use App\Models\Pendaftaran;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PendaftaranSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'tanggal_pendaftaran' => '2026-01-05',
                'siswa_id'            => 1,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-01-12',
                'siswa_id'            => 2,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-01-20',
                'siswa_id'            => 3,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-02-03',
                'siswa_id'            => 4,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-02-15',
                'siswa_id'            => 5,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-02-28',
                'siswa_id'            => 6,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-03-05',
                'siswa_id'            => 7,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-03-14',
                'siswa_id'            => 9,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-03-22',
                'siswa_id'            => 1,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
            [
                'tanggal_pendaftaran' => '2026-04-01',
                'siswa_id'            => 3,
                'created_by'          => 'Super Admin',
                'updated_by'          => 'Super Admin',
            ],
        ];

        foreach ($data as $item) {
            Pendaftaran::updateOrCreate(
                [
                    'tanggal_pendaftaran' => $item['tanggal_pendaftaran'],
                    'siswa_id'            => $item['siswa_id'],
                ],
                [
                    'created_by' => $item['created_by'],
                    'updated_by' => $item['updated_by'],
                ]
            );
        }
    }
}
