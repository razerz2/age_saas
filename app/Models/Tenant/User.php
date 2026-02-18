<?php

namespace App\Models\Tenant;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

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
        'is_system',
        'status',
        'modules',
        'role',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_enabled',
        'two_factor_method',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_doctor' => 'boolean',
        'is_system' => 'boolean',
        'modules' => 'array',
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'encrypted:array',
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
        return asset('connect_plus/assets/images/faces/default.jpg');
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
        // Admin pode ver todos
        if ($this->role === 'admin') {
            return true;
        }

        // Se o usuário é médico, não pode ver todos
        if ($this->role === 'doctor' || $this->is_doctor) {
            return false;
        }

        // Se não tem permissões específicas, pode ver todos
        return $this->doctorPermissions()->count() === 0;
    }

    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se o usuário é médico
     */
    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    /**
     * Verifica se o usuário é comum
     */
    public function isCommonUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Retorna o ID do médico associado ao usuário (se for role doctor)
     */
    public function getDoctorIdAttribute()
    {
        if ($this->role === 'doctor' && $this->doctor) {
            return $this->doctor->id;
        }
        return null;
    }

    /**
     * Verifica se o usuário tem acesso a um médico específico
     * Baseado no role e nas permissões
     */
    public function belongsToUser($doctorId): bool
    {
        // Admin pode acessar qualquer médico
        if ($this->role === 'admin') {
            return true;
        }

        // Se for role doctor, só pode acessar seu próprio médico
        if ($this->role === 'doctor') {
            return $this->doctor && (string) $this->doctor->id === (string) $doctorId;
        }

        // Usuário comum: verifica permissões
        return $this->allowedDoctors()->where('doctors.id', $doctorId)->exists();
    }

    /**
     * Gera uma nova chave secreta para autenticação de dois fatores
     */
    public function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        
        $this->two_factor_secret = encrypt($secret);
        $this->save();
        
        return $secret;
    }

    /**
     * Gera códigos de recuperação para autenticação de dois fatores
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        
        $this->two_factor_recovery_codes = $codes;
        $this->save();
        
        return $codes;
    }

    /**
     * Verifica se o código 2FA fornecido é válido
     */
    public function verifyTwoFactorCode(string $code): bool
    {
        if (!$this->two_factor_secret) {
            return false;
        }

        $google2fa = new Google2FA();
        $secret = decrypt($this->two_factor_secret);
        
        return $google2fa->verifyKey($secret, $code);
    }

    /**
     * Verifica se um código de recuperação é válido e o remove se for
     */
    public function useRecoveryCode(string $code): bool
    {
        if (!$this->two_factor_recovery_codes) {
            return false;
        }

        $codes = $this->two_factor_recovery_codes;
        $index = array_search(strtoupper($code), array_map('strtoupper', $codes));
        
        if ($index !== false) {
            unset($codes[$index]);
            $this->two_factor_recovery_codes = array_values($codes);
            $this->save();
            return true;
        }
        
        return false;
    }

    /**
     * Ativa a autenticação de dois fatores
     */
    public function enableTwoFactor(): void
    {
        $this->two_factor_enabled = true;
        // Define método padrão como email se não estiver definido
        if (!$this->two_factor_method) {
            $this->two_factor_method = 'email';
        }
        $this->save();
    }

    /**
     * Desativa a autenticação de dois fatores
     */
    public function disableTwoFactor(): void
    {
        $this->two_factor_enabled = false;
        $this->two_factor_secret = null;
        $this->two_factor_recovery_codes = null;
        $this->save();
    }

    /**
     * Verifica se o 2FA está habilitado
     */
    public function hasTwoFactorEnabled(): bool
    {
        if (!$this->two_factor_enabled) {
            return false;
        }
        
        // Para TOTP, precisa ter secret
        if ($this->two_factor_method === 'totp') {
            return !empty($this->two_factor_secret);
        }
        
        // Para email/whatsapp, só precisa estar habilitado e ter método definido
        return !empty($this->two_factor_method);
    }

    /**
     * Retorna a URL QR Code para configuração do 2FA
     */
    public function getTwoFactorQrCodeUrl(): string
    {
        if (!$this->two_factor_secret) {
            return '';
        }

        $google2fa = new Google2FA();
        $secret = decrypt($this->two_factor_secret);
        
        $companyName = tenant()->trade_name ?? config('app.name', 'Sistema');
        $companyEmail = $this->email;
        
        return $google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $secret
        );
    }
}
