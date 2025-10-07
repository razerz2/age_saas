<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('medical_specialties_catalog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->unique();
            $table->string('code')->nullable(); // CBO opcional
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('medical_specialties_catalog');
    }
};
