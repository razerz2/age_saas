<?php

namespace App\Services;

use App\Models\Platform\TwoFactorCode as PlatformTwoFactorCode;
use App\Models\Tenant\TwoFactorCode as TenantTwoFactorCode;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TwoFactorCodeService
{
    /**
     * Gera um código de 6 dígitos e salva no banco
     *
     * @param User $user
     * @param string $method 'email' ou 'whatsapp'
     * @return string Código gerado
     */
    public function generateCode(User $user, string $method): string
    {
        // Gera código de 6 dígitos
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Determina qual model usar baseado na conexão do usuário
        $isTenant = $user->getConnectionName() === 'tenant';
        $modelClass = $isTenant ? TenantTwoFactorCode::class : PlatformTwoFactorCode::class;

        // Remove códigos antigos do usuário
        $modelClass::where('user_id', $user->id)
            ->where('method', $method)
            ->delete();

        // Cria novo código (expira em 10 minutos)
        $modelClass::create([
            'user_id' => $user->id,
            'code' => $code,
            'method' => $method,
            'expires_at' => Carbon::now()->addMinutes(10),
            'used' => false,
        ]);

        Log::info('Código 2FA gerado', [
            'user_id' => $user->id,
            'method' => $method,
            'connection' => $isTenant ? 'tenant' : 'platform'
        ]);

        return $code;
    }

    /**
     * Verifica se um código é válido
     *
     * @param User $user
     * @param string $code
     * @param string $method
     * @return bool
     */
    public function verifyCode(User $user, string $code, string $method): bool
    {
        $isTenant = $user->getConnectionName() === 'tenant';
        $modelClass = $isTenant ? TenantTwoFactorCode::class : PlatformTwoFactorCode::class;

        $twoFactorCode = $modelClass::where('user_id', $user->id)
            ->where('code', $code)
            ->where('method', $method)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($twoFactorCode && $twoFactorCode->isValid()) {
            $twoFactorCode->markAsUsed();
            return true;
        }

        return false;
    }

    /**
     * Limpa códigos expirados
     *
     * @param string|null $connection 'tenant' ou null para platform
     * @return int Número de códigos removidos
     */
    public function cleanupExpiredCodes(?string $connection = null): int
    {
        if ($connection === 'tenant') {
            return TenantTwoFactorCode::cleanup();
        }

        return PlatformTwoFactorCode::cleanup();
    }
}

