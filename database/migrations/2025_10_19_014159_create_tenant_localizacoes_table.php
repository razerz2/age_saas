<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('tenant_localizacoes', function (Blueprint $table) {
            $table->bigIncrements('id_localizacao');

            // ⚙️ Corrigido para UUID (compatível com tenants.id)
            $table->uuid('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->string('endereco');
            $table->string('n_endereco')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep', 15)->nullable();

            $table->unsignedBigInteger('pais_id')->nullable();
            $table->unsignedBigInteger('estado_id')->nullable();
            $table->unsignedBigInteger('cidade_id')->nullable();

            $table->foreign('pais_id')->references('id_pais')->on('paises')->nullOnDelete();
            $table->foreign('estado_id')->references('id_estado')->on('estados')->nullOnDelete();
            $table->foreign('cidade_id')->references('id_cidade')->on('cidades')->nullOnDelete();

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('tenant_localizacoes');
    }
};
