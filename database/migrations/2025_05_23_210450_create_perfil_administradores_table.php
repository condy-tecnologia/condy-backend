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
        Schema::create('perfil_administradores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('razao_social_empresa')->nullable();
            $table->string('nome_fantasia_empresa')->nullable();
            $table->string('responsavel_empresa_nome')->nullable();
            $table->string('cep_empresa')->nullable();
            $table->text('endereco_empresa')->nullable();
            $table->json('contrato_prestacao_servico_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfil_administradores');
    }
};
