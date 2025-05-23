<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Condominio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'condominios';

    protected $fillable = [
        'manager_user_id',
        'cnpj',
        'nome_fantasia',
        'razao_social',
        'cep',
        'endereco',
        'cidade',
        'uf',
        'regime_tributario',
        'estatuto_url',
        'quantidade_moradias',
        'dados_pagamento_plano',
        'areas_comuns',
    ];

    protected $casts = [
        'dados_pagamento_plano' => 'json',
        'areas_comuns' => 'json',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    public function ativos()
    {
        return $this->hasMany(Ativo::class);
    }

    public function solicitacoes()
    {
        return $this->hasMany(SolicitacaoServico::class);
    }
}
