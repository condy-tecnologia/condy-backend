<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerfilSindico extends Model
{
    use HasFactory;

    protected $table = 'perfil_sindicos';

    protected $fillable = [
        'user_id',
        'periodo_mandato_inicio',
        'periodo_mandato_fim',
        'subsyndic_info',
    ];

    protected $casts = [
        'periodo_mandato_inicio' => 'date',
        'periodo_mandato_fim' => 'date',
        'subsyndic_info' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
