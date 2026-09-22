<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_akses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            $table->foreignId('grup_id')->constrained('grup')->cascadeOnDelete();
            $table->integer('view')->default(0);
            $table->integer('add')->default(0);
            $table->integer('edit')->default(0);
            $table->integer('delete')->default(0);

            $table->unique(['menu_id', 'grup_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_akses');
    }
};
