<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'cpf_cnpj',
        'whatsapp',
        'email',
        'password',
        'user_type',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relacionamentos com perfis
    public function perfilPrestador()
    {
        return $this->hasOne(PerfilPrestador::class);
    }

    public function perfilSindico()
    {
        return $this->hasOne(PerfilSindico::class);
    }

    public function perfilAdministrador()
    {
        return $this->hasOne(PerfilAdministrador::class);
    }

    // Relacionamentos com condomínios
    public function condominiosGerenciados()
    {
        return $this->hasMany(Condominio::class, 'manager_user_id');
    }

    // Relacionamentos com solicitações
    public function solicitacoesFeitas()
    {
        return $this->hasMany(SolicitacaoServico::class, 'requester_user_id');
    }

    public function solicitacoesDesignadas()
    {
        return $this->hasMany(SolicitacaoServico::class, 'assigned_provider_user_id');
    }

    public function solicitacoesEncaminhadas()
    {
        return $this->hasMany(SolicitacaoServico::class, 'admin_forwarder_user_id');
    }

    // Relacionamento com aceites de termos
    public function aceitesTermos()
    {
        return $this->hasMany(AceiteTermo::class);
    }
}
