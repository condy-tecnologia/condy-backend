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
        Schema::create('solicitacoes_servico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('condominio_id')->constrained('condominios')->onDelete('cascade');
            $table->foreignId('ativo_id')->nullable()->constrained('ativos')->onDelete('set null');
            $table->foreignId('requester_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_provider_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('admin_forwarder_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('numero_chamado')->unique();
            $table->longText('descricao_ocorrido');
            $table->json('fotos_videos_urls')->nullable();
            $table->longText('informacoes_adicionais')->nullable();
            $table->enum('prioridade', ['BAIXA', 'MEDIA', 'ALTA'])->default('MEDIA');
            $table->enum('escopo', ['ORCAMENTO', 'SERVICO_IMEDIATO']);
            $table->enum('status', ['NOVO', 'EM_ATENDIMENTO', 'CONCLUIDO'])->default('NOVO');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitacoes_servico');
    }
};
