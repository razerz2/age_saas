<?php

namespace App\Helpers;

use App\Http\Controllers\Platform\EmailLayoutController;
use App\Models\Platform\EmailLayout;

class EmailLayoutHelper
{
    /**
     * Aplica o layout de email ativo a qualquer conteúdo HTML
     * 
     * @param string $content Conteúdo HTML a ser envolvido pelo layout
     * @param array $data Dados adicionais para substituição de variáveis
     * @return string HTML completo com layout aplicado
     */
    public static function apply(string $content, array $data = []): string
    {
        return EmailLayoutController::applyLayoutToContent($content, $data);
    }

    /**
     * Renderiza apenas o conteúdo HTML extraindo do body de uma view
     * Útil para aplicar layout em views Blade
     */
    public static function renderViewContent(string $view, array $data = []): string
    {
        try {
            $html = view($view, $data)->render();
            
            // Remove DOCTYPE, html, head, body tags se existirem
            $html = preg_replace('/<!DOCTYPE[^>]*>/i', '', $html);
            $html = preg_replace('/<html[^>]*>/i', '', $html);
            $html = preg_replace('/<\/html>/i', '', $html);
            $html = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $html);
            $html = preg_replace('/<body[^>]*>/i', '', $html);
            $html = preg_replace('/<\/body>/i', '', $html);
            
            // Remove tags <style> se existirem (já que o layout tem seus próprios estilos)
            $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html);
            
            $html = trim($html);
            
            // Garante que temos dados básicos para o layout
            if (!isset($data['app_name'])) {
                $data['app_name'] = config('app.name', 'Sistema de Agendamento');
            }
            
            // Aplica o layout com os dados
            return self::apply($html, $data);
        } catch (\Throwable $e) {
            \Log::error('Erro ao renderizar view de email', [
                'view' => $view,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

