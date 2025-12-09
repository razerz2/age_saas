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
        Schema::table('email_layouts', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('display_name')->comment('URL do logo para usar no cabeçalho');
            $table->integer('logo_width')->default(200)->after('logo_url')->comment('Largura do logo em pixels (padrão: 200px)');
            $table->integer('logo_height')->nullable()->after('logo_width')->comment('Altura do logo em pixels (null = proporcional)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_layouts', function (Blueprint $table) {
            $table->dropColumn(['logo_url', 'logo_width', 'logo_height']);
        });
    }
};
