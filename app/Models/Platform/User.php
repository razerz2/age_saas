<?php

namespace App\Models\Platform;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos que podem ser atribuídos em massa.
     */
    protected $fillable = [
        'name',
        'name_full',
        'email',
        'password',
        'email_verified_at',
        'status',
        'modules', // ✅ agora o sistema pode atualizar os módulos do usuário
    ];

    /**
     * Campos ocultos ao serializar o model (ex: em JSON ou arrays).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Tipos de conversão automática de atributos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'modules' => 'array', // ✅ garante leitura e escrita automáticas como array JSON
    ];

    /**
     * Define um mutator automático para criptografar a senha sempre que alterada.
     */
    public function setPasswordAttribute($value)
    {
        // Só aplica hash se o valor não estiver vazio e ainda não estiver criptografado
        if (!empty($value) && !str_starts_with($value, '$2y$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }
}
