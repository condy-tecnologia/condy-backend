<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AceiteTermo extends Model
{
    use HasFactory;

    protected $table = 'aceites_termos';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'temp_user_identifier',
        'terms_version',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
