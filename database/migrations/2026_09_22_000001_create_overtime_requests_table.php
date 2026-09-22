<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::create('overtime_requests',function(Blueprint $table){$table->id();$table->foreignId('karyawan_id')->constrained('karyawans')->cascadeOnDelete();$table->date('tanggal');$table->time('jam_mulai');$table->time('jam_selesai');$table->text('keperluan');$table->string('status')->default('Pending');$table->text('catatan_approval')->nullable();$table->timestamps();}); } public function down(): void { Schema::dropIfExists('overtime_requests'); } };
