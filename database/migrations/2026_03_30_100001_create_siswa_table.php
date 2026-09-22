<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_siswa');
            $table->string('nik');
            $table->enum('jenis_kelamin', ['laki_laki', 'perempuan']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('agama');
            $table->string('agama_lainnya');
            $table->string('email', 255);
            $table->string('no_telepon');
            $table->string('alamat');
            $table->string('jenis_tinggal');
            $table->string('kota')->nullable();
            $table->string('provinsi')->nullable();
            $table->string('pendidikan_terakhir');
            $table->string('abk');
            $table->string('pekerjaan');
            $table->string('foto')->nullable();
            $table->boolean('status_siswa')->default(0);
            $table->string('upload_ktp')->nullable();
            $table->string('upload_kk')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
