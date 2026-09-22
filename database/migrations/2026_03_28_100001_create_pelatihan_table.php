<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelatihan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pendaftaran');
            $table->string('nama_perusahaan', 255);
            $table->string('alamat', 255);
            $table->string('kota', 255);
            $table->string('provinsi', 255);
            $table->string('nama_pic', 255);
            $table->string('telepon', 255);
            $table->string('email', 255);
            $table->enum('tempat_daftar', ['nginden', 'tubanan']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelatihan');
    }
};
