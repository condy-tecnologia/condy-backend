<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilPrestador extends Model
{
    use HasFactory;

    protected $table = 'perfil_prestadores';

    protected $fillable = [
        'user_id',
        'nome_fantasia',
        'razao_social',
        'cep',
        'endereco',
        'cidade',
        'raio_atuacao',
        'segmentos_atuacao',
        'regime_tributario',
        'data_nascimento',
    ];

    protected $casts = [
        'raio_atuacao' => 'json',
        'segmentos_atuacao' => 'json',
        'data_nascimento' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
