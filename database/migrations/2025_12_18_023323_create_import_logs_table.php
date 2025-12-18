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
        Schema::connection('pgsql')->create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_hash')->unique();
            $table->string('type')->default('clinic_import');
            $table->string('status')->default('pending')->index();
            $table->integer('total_rows')->default(0);
            $table->integer('processed_rows')->default(0);
            $table->integer('error_count')->default(0);
            $table->integer('skipped_count')->default(0);
            $table->string('temp_path')->nullable();
            $table->jsonb('config')->nullable();
            $table->json('summary')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('import_logs');
    }
};
