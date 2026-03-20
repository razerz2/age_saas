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
        Schema::table('estados', function (Blueprint $table) {
            if (! Schema::hasColumn('estados', 'ibge_id')) {
                $table->unsignedBigInteger('ibge_id')->nullable()->after('nome_estado');
            }
        });

        Schema::table('cidades', function (Blueprint $table) {
            if (! Schema::hasColumn('cidades', 'ibge_id')) {
                $table->unsignedBigInteger('ibge_id')->nullable()->after('nome_cidade');
            }
        });

        Schema::table('estados', function (Blueprint $table) {
            $table->index('ibge_id', 'estados_ibge_id_index');
        });

        Schema::table('cidades', function (Blueprint $table) {
            $table->index('ibge_id', 'cidades_ibge_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estados', function (Blueprint $table) {
            $table->dropIndex('estados_ibge_id_index');
            if (Schema::hasColumn('estados', 'ibge_id')) {
                $table->dropColumn('ibge_id');
            }
        });

        Schema::table('cidades', function (Blueprint $table) {
            $table->dropIndex('cidades_ibge_id_index');
            if (Schema::hasColumn('cidades', 'ibge_id')) {
                $table->dropColumn('ibge_id');
            }
        });
    }
};
