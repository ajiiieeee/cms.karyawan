<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->change();
            $table->string('pekerjaan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            $table->string('email', 255)->nullable(false)->change();
            $table->string('pekerjaan')->nullable(false)->change();
        });
    }
};
