<?php

namespace App\Models\Platform;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Collection;

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
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_enabled',
        'two_factor_method',
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
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'encrypted:array',
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
        
        $companyName = config('app.name', 'Sistema');
        $companyEmail = $this->email;
        
        return $google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $secret
        );
    }
}
