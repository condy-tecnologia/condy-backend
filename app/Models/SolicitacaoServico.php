<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SolicitacaoServico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitacoes_servico';

    protected $fillable = [
        'condominio_id',
        'ativo_id',
        'requester_user_id',
        'assigned_provider_user_id',
        'admin_forwarder_user_id',
        'numero_chamado',
        'descricao_ocorrido',
        'fotos_videos_urls',
        'informacoes_adicionais',
        'prioridade',
        'escopo',
        'status',
    ];

    protected $casts = [
        'fotos_videos_urls' => 'json',
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class);
    }

    public function ativo()
    {
        return $this->belongsTo(Ativo::class);
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function prestadorDesignado()
    {
        return $this->belongsTo(User::class, 'assigned_provider_user_id');
    }

    public function adminEncaminhador()
    {
        return $this->belongsTo(User::class, 'admin_forwarder_user_id');
    }
}
