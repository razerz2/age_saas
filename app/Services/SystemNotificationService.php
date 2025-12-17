<?php

namespace App\Services;

use App\Models\Platform\SystemNotification;
use Illuminate\Support\Facades\Log;

class SystemNotificationService
{
    /**
     * Mapeia contextos para tipos de configuração
     */
    private static function getConfigKeyForContext(?string $context): ?string
    {
        if (!$context) {
            return null;
        }

        // Mapeia contextos para chaves de configuração
        $contextMap = [
            'invoice' => 'notifications.types.invoice',
            'payment' => 'notifications.types.payment',
            'subscription' => 'notifications.types.subscription',
            'tenant' => 'notifications.types.tenant',
            'customer' => 'notifications.types.tenant', // Clientes são relacionados a tenants
            'webhook' => 'notifications.types.webhook',
        ];

        // Para comandos, verifica se o título ou mensagem contém palavras-chave
        $commandKeywords = ['comando', 'command', 'execução', 'processamento', 'geração automática'];
        
        return $contextMap[$context] ?? null;
    }

    /**
     * Verifica se é uma notificação de pagamento
     */
    private static function isPaymentNotification(string $title, string $message): bool
    {
        $paymentKeywords = [
            'pagamento confirmado',
            'pagamento estornado',
            'pagamento recebido',
            'payment confirmed',
            'payment refunded',
        ];
        
        $textToCheck = strtolower($title . ' ' . $message);
        
        foreach ($paymentKeywords as $keyword) {
            if (str_contains($textToCheck, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica se é uma notificação sobre bloqueio/suspensão de tenant
     */
    private static function isTenantBlockNotification(string $title, string $message): bool
    {
        $blockKeywords = [
            'tenant suspenso',
            'tenants suspensos',
            'tenant bloqueado',
            'tenants bloqueados',
            'suspenso imediatamente',
            'suspensos imediatamente',
            'bloqueio',
            'suspensão',
        ];
        
        $textToCheck = strtolower($title . ' ' . $message);
        
        foreach ($blockKeywords as $keyword) {
            if (str_contains($textToCheck, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica se é uma notificação de comando executado
     */
    private static function isCommandNotification(string $title, string $message): bool
    {
        $commandKeywords = [
            'execução do processamento',
            'processamento de assinaturas concluído',
            'processamento de recovery',
            'verificação de faturas',
            'geração automática de faturas',
            'notificações de faturas próximas',
            'purga de tenants',
            'comando',
            'command',
            'execução',
            'processamento concluído',
            'geração automática',
        ];
        
        $textToCheck = strtolower($title . ' ' . $message);
        
        foreach ($commandKeywords as $keyword) {
            if (str_contains($textToCheck, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verifica se um tipo de notificação está habilitado
     */
    private static function isNotificationTypeEnabled(?string $context, string $title = '', string $message = ''): bool
    {
        // Se não há contexto, permite (notificações genéricas)
        if (!$context) {
            return true;
        }

        // Verifica primeiro se é uma notificação de pagamento
        // (mesmo que tenha contexto 'invoice', se for sobre pagamento, usa 'payment')
        if (self::isPaymentNotification($title, $message)) {
            $configKey = 'notifications.types.payment';
            return sysconfig($configKey, '1') === '1';
        }

        // Verifica se é uma notificação sobre bloqueio/suspensão de tenant
        // (mesmo que tenha contexto 'invoice', se for sobre bloqueio, usa 'tenant')
        if (self::isTenantBlockNotification($title, $message)) {
            $configKey = 'notifications.types.tenant';
            return sysconfig($configKey, '1') === '1';
        }

        // Verifica se é uma notificação de comando executado
        // (mesmo que tenha contexto 'invoice' ou 'subscription', se for resultado de comando, usa 'command')
        if (self::isCommandNotification($title, $message)) {
            $configKey = 'notifications.types.command';
            return sysconfig($configKey, '1') === '1';
        }

        $configKey = self::getConfigKeyForContext($context);
        
        // Se não há mapeamento, permite (novos tipos de contexto)
        if (!$configKey) {
            return true;
        }

        // Verifica a configuração (padrão: true para a maioria, false para webhook)
        $defaultValue = $configKey === 'notifications.types.webhook' ? '0' : '1';
        return sysconfig($configKey, $defaultValue) === '1';
    }

    /**
     * Cria uma notificação do sistema se o tipo estiver habilitado
     */
    public static function notify(string $title, ?string $message = null, ?string $context = null, string $level = 'info'): void
    {
        // Verifica se o tipo de notificação está habilitado
        if (!self::isNotificationTypeEnabled($context, $title, $message ?? '')) {
            Log::debug("📢 System Notification ignorada (tipo desabilitado): {$title}", [
                'context' => $context,
                'config_key' => self::getConfigKeyForContext($context),
            ]);
            return;
        }

        SystemNotification::create([
            'title'   => $title,
            'message' => $message,
            'context' => $context,
            'level'   => $level,
        ]);

        Log::info("📢 System Notification: {$title}", ['context' => $context]);
    }
}