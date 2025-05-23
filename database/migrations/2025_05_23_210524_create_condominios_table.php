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
        Schema::create('condominios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_user_id')->constrained('users')->onDelete('cascade');
            $table->string('cnpj')->unique();
            $table->string('nome_fantasia');
            $table->string('razao_social');
            $table->string('cep');
            $table->text('endereco');
            $table->string('cidade');
            $table->string('uf', 2);
            $table->string('regime_tributario')->nullable();
            $table->string('estatuto_url')->nullable();
            $table->integer('quantidade_moradias');
            $table->json('dados_pagamento_plano')->nullable();
            $table->json('areas_comuns')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condominios');
    }
};
