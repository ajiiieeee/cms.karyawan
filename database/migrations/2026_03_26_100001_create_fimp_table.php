<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fimp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->date('tanggal_awal');
            $table->date('tanggal_akhir');
            $table->integer('total_hari');
            $table->unsignedBigInteger('karyawan_pengganti');
            $table->text('keperluan');
            $table->string('reject_statement')->nullable();
            $table->boolean('pengganti_mengetahui')->default(false);
            $table->boolean('pengganti_bersedia')->default(false);
            $table->integer('status_pengajuan')->default(0);
            $table->string('status_approval')->default('Pending');
            $table->date('created_date')->nullable();
            $table->date('updated_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('karyawan_id')->references('id')->on('karyawans')->onDelete('cascade');
            $table->foreign('karyawan_pengganti')->references('id')->on('karyawans')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fimp');
    }
};
