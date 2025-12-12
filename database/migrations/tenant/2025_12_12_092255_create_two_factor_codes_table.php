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
        if (!Schema::hasTable('two_factor_codes')) {
            Schema::create('two_factor_codes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('code', 6);
                $table->enum('method', ['email', 'whatsapp']);
                $table->timestamp('expires_at');
                $table->boolean('used')->default(false);
                $table->timestamps();

                $table->index(['user_id', 'code', 'used']);
                $table->index('expires_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_codes');
    }
};
