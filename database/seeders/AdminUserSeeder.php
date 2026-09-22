<?php

namespace Database\Seeders;

use App\Models\Grup;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminGrup = Grup::where('nama_grup', 'Super Admin')->first();

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'nama'      => 'Super Admin',
                'email'     => 'superadmin@creativemedia.id',
                'password'  => 'password123',
                'grup_id'   => $superAdminGrup?->id,
                'is_active' => true,
            ]
        );
    }
}
