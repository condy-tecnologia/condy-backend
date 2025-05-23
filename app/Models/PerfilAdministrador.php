<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilAdministrador extends Model
{
    use HasFactory;

    protected $table = 'perfil_administradores';

    protected $fillable = [
        'user_id',
        'razao_social_empresa',
        'nome_fantasia_empresa',
        'responsavel_empresa_nome',
        'cep_empresa',
        'endereco_empresa',
        'contrato_prestacao_servico_info',
    ];

    protected $casts = [
        'contrato_prestacao_servico_info' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
