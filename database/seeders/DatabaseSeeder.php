<?php
namespace Database\Seeders;
use App\Models\Karyawan;
use App\Models\KategoriCuti;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class DatabaseSeeder extends Seeder { public function run(): void { foreach (['Cuti Tahunan','Cuti Sakit','Cuti Keperluan Pribadi'] as $name) { KategoriCuti::firstOrCreate(['nama_kategori'=>$name]); } $employee=Karyawan::firstOrCreate(['username'=>'employee'],['nama_karyawan'=>'Karyawan Demo','nik'=>'000000000000000001','jenis_kelamin'=>'laki-laki','tanggal_lahir'=>'1990-01-01','tempat_lahir'=>'Jakarta','email'=>'employee@example.test','alamat'=>'Jakarta','telefon'=>'081234567890','pendidikan_terakhir'=>'S1','tahun_lulus'=>2012,'jabatan'=>'admin','tanggal_masuk'=>now()->toDateString(),'status_akun'=>'aktif','nama_keluarga'=>'Kontak Darurat','hubungan_keluarga'=>'Keluarga','password'=>Hash::make('password')]); User::firstOrCreate(['username'=>'employee'],['nama'=>$employee->nama_karyawan,'email'=>$employee->email,'password'=>Hash::make('password'),'is_active'=>true]); } }
