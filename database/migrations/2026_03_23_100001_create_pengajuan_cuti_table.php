<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->unsignedBigInteger('jenis_cuti');
            $table->string('jumlah_hari', 10);
            $table->integer('status_approval')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('reject_statement')->nullable();
            $table->string('status_pengajuan')->default('Pending');
            $table->date('created_date')->nullable();
            $table->date('updated_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawans')->onDelete('cascade');
            $table->foreign('jenis_cuti')->references('id')->on('kategori_cuti')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};
