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
        Schema::create('ativos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominio_id')->constrained('condominios')->onDelete('cascade');
            $table->string('asset_code')->unique();
            $table->text('descricao_ativo');
            $table->string('local_instalacao');
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->date('data_instalacao')->nullable();
            $table->decimal('valor_compra', 10, 2)->nullable();
            $table->boolean('garantia')->default(false);
            $table->date('garantia_validade')->nullable();
            $table->json('garantia_fornecedor_info')->nullable();
            $table->boolean('contrato_manutencao')->default(false);
            $table->date('contrato_validade')->nullable();
            $table->json('contrato_fornecedor_info')->nullable();
            $table->json('relatorio_fotografico_urls')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ativos');
    }
};
