<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_kelas', function (Blueprint $table) {
            $table->id();
            $table->enum('nama_kategori', ['Reguler Class', 'Inspiration Class', 'Business Class', 'Executive Class']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_kelas');
    }
};
