<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sertifikat_bulk_sign_logs', function (Blueprint $table) {
            $table->id();
            $table->string('job_id', 255);
            $table->foreignId('sertifikat_id')->constrained('sertifikat')->cascadeOnDelete();
            $table->string('action', 50);          // 'signed' | 'failed'
            $table->text('error_message')->nullable();
            $table->unsignedInteger('batch_number')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('job_id');
        });

        // Performance indexes on sertifikat table (if not already present)
        Schema::table('sertifikat', function (Blueprint $table) {
            // Composite index for the main bulk-sign filter query
            try {
                $table->index(['status', 'signature_date'], 'idx_sertifikat_status_sig');
            } catch (\Exception $e) {
                // Index may already exist — skip silently
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sertifikat_bulk_sign_logs');

        Schema::table('sertifikat', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_sertifikat_status_sig');
            } catch (\Exception $e) {
                // Index may not exist
            }
        });
    }
};
