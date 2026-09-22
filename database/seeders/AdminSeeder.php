<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cek apakah admin sudah ada
        if (!Admin::where('username', 'admin')->exists()) {
            Admin::create([
                'username' => 'admin',
                'password' => Hash::make('password123'),
                'email' => 'admin@example.com',
                'nama' => 'Administrator',
                'grup_id' => 1,
                'status' => 3,
                'last_created_date' => now(),
                'last_update_date' => now(),
                'created_by' => 'Super Admin',
                'last_update_by' => 'Super Admin',
                'last_login_user' => now(),
            ]);
        }
    }
}
