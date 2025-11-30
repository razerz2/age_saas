<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'tenant';
    protected $table = 'users';

    protected $fillable = [
        'tenant_id',  // <-- ADICIONADO AQUI
        'name',
        'name_full',
        'telefone',
        'email',
        'avatar',
        'password',
        'is_doctor',
        'status',
        'modules',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_doctor' => 'boolean',
        'modules' => 'array',
        'email_verified_at' => 'datetime',
    ];

    public $timestamps = true;

    public function setPasswordAttribute($value)
    {
        // Só aplica hash se o valor não estiver vazio e ainda não estiver criptografado
        // Verifica se já está no formato Bcrypt (começa com $2y$, $2a$ ou $2b$)
        if (!empty($value) && !str_starts_with($value, '$2y$') && !str_starts_with($value, '$2a$') && !str_starts_with($value, '$2b$')) {
            $this->attributes['password'] = Hash::make($value);
        } else {
            $this->attributes['password'] = $value;
        }
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name_full ?? $this->name;
    }

    /**
     * Retorna a URL do avatar do usuário ou uma imagem padrão.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return asset('connect_plus/assets/images/faces/face28.png');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Relação correta com o Tenant da plataforma
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Platform\Tenant::class, 'tenant_id', 'id');
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Relacionamento com permissões de médicos
     * Usuários não médicos podem ter permissões para visualizar médicos específicos
     */
    public function doctorPermissions()
    {
        return $this->hasMany(UserDoctorPermission::class);
    }

    /**
     * Médicos que o usuário tem permissão para visualizar
     */
    public function allowedDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'user_doctor_permissions', 'user_id', 'doctor_id')
            ->withTimestamps();
    }

    /**
     * Verifica se o usuário tem permissão para visualizar um médico específico
     */
    public function canViewDoctor($doctorId): bool
    {
        // Se o usuário é médico, só pode ver a si mesmo
        if ($this->is_doctor && $this->doctor) {
            return (string) $this->doctor->id === (string) $doctorId;
        }

        // Se não é médico, verifica se tem permissão específica usando o relacionamento belongsToMany
        return $this->allowedDoctors()->where('doctors.id', $doctorId)->exists();
    }

    /**
     * Verifica se o usuário pode visualizar todos os médicos (sem restrições)
     */
    public function canViewAllDoctors(): bool
    {
        // Se o usuário é médico, não pode ver todos
        if ($this->is_doctor) {
            return false;
        }

        // Se não tem permissões específicas, pode ver todos
        return $this->doctorPermissions()->count() === 0;
    }
}
