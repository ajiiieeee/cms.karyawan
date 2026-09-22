<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_karyawan', 100);
            $table->string('nik', 18);
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->date('tanggal_lahir');
            $table->string('tempat_lahir');
            $table->string('email', 255);
            $table->string('alamat', 255);
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('telefon', 20);
            $table->string('telefon_alternatif', 20)->nullable();
            $table->string('pendidikan_terakhir', 100);
            $table->string('lembaga_pendidikan', 100)->nullable();
            $table->year('tahun_lulus');
            $table->enum('jabatan', ['cso', 'admin', 'manager', 'programmer', 'desaingrafis', 'hrd', 'direktur', 'trainer', 'bd', 'bc', 'itsupport', 'contentcreator']);
            $table->date('tanggal_masuk');
            $table->string('foto')->nullable();
            $table->enum('status_akun', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->string('nama_keluarga', 100);
            $table->string('alamat_keluarga', 100)->nullable();
            $table->string('pendidikan_keluarga', 100)->nullable();
            $table->string('hubungan_keluarga', 100);
            $table->string('telefon_keluarga', 100)->nullable();
            $table->string('username', 255);
            $table->string('password', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
