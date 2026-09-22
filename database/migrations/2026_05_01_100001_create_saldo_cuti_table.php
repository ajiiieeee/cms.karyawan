<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saldo_cuti', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->integer('total_cuti')->unsigned()->comment('Jumlah hari cuti yang didapatkan di periode ini');
            $table->date('periode_mulai')->comment('Tanggal mulai periode cuti');
            $table->date('periode_selesai')->comment('Tanggal selesai periode cuti');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saldo_cuti');
    }
};
