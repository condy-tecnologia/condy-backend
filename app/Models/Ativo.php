<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ativo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ativos';

    protected $fillable = [
        'condominio_id',
        'asset_code',
        'descricao_ativo',
        'local_instalacao',
        'marca',
        'modelo',
        'data_instalacao',
        'valor_compra',
        'garantia',
        'garantia_validade',
        'garantia_fornecedor_info',
        'contrato_manutencao',
        'contrato_validade',
        'contrato_fornecedor_info',
        'relatorio_fotografico_urls',
    ];

    protected $casts = [
        'data_instalacao' => 'date',
        'garantia_validade' => 'date',
        'contrato_validade' => 'date',
        'garantia' => 'boolean',
        'contrato_manutencao' => 'boolean',
        'garantia_fornecedor_info' => 'json',
        'contrato_fornecedor_info' => 'json',
        'relatorio_fotografico_urls' => 'json',
        'valor_compra' => 'decimal:2',
    ];

    public function condominio()
    {
        return $this->belongsTo(Condominio::class);
    }

    public function solicitacoes()
    {
        return $this->hasMany(SolicitacaoServico::class);
    }
}
