<?php

namespace App\Services;

use App\Models\Platform\NotificationTemplate;
use App\Models\Platform\EmailLayout;
use App\Services\DTO\RenderedMessageDTO;

class TemplateRenderer
{
    /**
     * Renderiza um template de notificação substituindo placeholders
     */
    public function render(string $templateName, array $data = []): RenderedMessageDTO
    {
        $template = NotificationTemplate::where('name', $templateName)->first();

        if (!$template) {
            throw new \Exception("Template '{$templateName}' não encontrado.");
        }

        // Se template desabilitado, usar valores padrão
        $subject = $template->enabled ? $template->subject : $template->default_subject;
        $body = $template->enabled ? $template->body : $template->default_body;

        // Se ainda estiver vazio, usar padrão
        $subject = $subject ?? $template->default_subject;
        $body = $body ?? $template->default_body;

        // Substituir placeholders
        $renderedSubject = $this->replacePlaceholders($subject ?? '', $data);
        $renderedBody = $this->replacePlaceholders($body, $data);

        // Se for email, aplicar layout profissional
        if ($template->channel === 'email') {
            $renderedBody = $this->applyEmailLayout($renderedBody, $data);
        }

        return new RenderedMessageDTO(
            subject: $renderedSubject,
            body: $renderedBody
        );
    }

    /**
     * Aplica o layout de email ao conteúdo
     */
    protected function applyEmailLayout(string $content, array $data = []): string
    {
        $layout = EmailLayout::getActiveLayout();
        
        // Adiciona app_name aos dados se não existir
        if (!isset($data['app_name'])) {
            $data['app_name'] = config('app.name', 'Sistema de Agendamento');
        }

        // Adiciona logo_url aos dados se o layout tiver logo
        if (!isset($data['logo_url']) && $layout->logo_url) {
            $data['logo_url'] = $layout->logo_url;
        }
        if (!isset($data['logo_width'])) {
            $data['logo_width'] = $layout->logo_width ?? 200;
        }
        if (!isset($data['logo_height']) && $layout->logo_height) {
            $data['logo_height'] = $layout->logo_height;
        }

        // Processa logo no header ANTES de substituir outras variáveis
        // Isso garante que {{app_name}} seja substituído por logo se houver
        $header = $this->processLogoInHeader($layout->header ?? '', $layout, $data);
        
        // Agora substitui as variáveis restantes no header e footer
        $header = $this->replacePlaceholders($header, $data);
        $footer = $this->replacePlaceholders($layout->footer ?? '', $data);

        // Monta o HTML completo do email
        return '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificação</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: ' . $layout->background_color . ';">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: ' . $layout->background_color . ';">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td>
                            ' . $header . '
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px; color: ' . $layout->text_color . '; line-height: 1.6;">
                            ' . $content . '
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td>
                            ' . $footer . '
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }

    /**
     * Substitui placeholders {{variavel}} pelos valores fornecidos
     */
    protected function replacePlaceholders(string $content, array $data): string
    {
        return preg_replace_callback('/\{\{(\w+)\}\}/', function ($matches) use ($data) {
            $key = $matches[1];
            return $data[$key] ?? '';
        }, $content);
    }

    /**
     * Processa o logo no header, substituindo placeholders ou adicionando se necessário
     */
    protected function processLogoInHeader(string $header, $layout, array $data): string
    {
        if (empty($layout->logo_url)) {
            // Remove blocos condicionais
            $header = preg_replace('/@if\(logo_url\)[\s\S]*?@else([\s\S]*?)@endif/', '$1', $header);
            $header = preg_replace('/@if\(logo_url\)[\s\S]*?@endif/', '', $header);
            return $header;
        }

        $logoUrl = $this->ensureAbsoluteUrl($layout->logo_url);
        $logoWidth = $layout->logo_width ?? 200;
        $logoHeight = $layout->logo_height ? $layout->logo_height . 'px' : 'auto';

        // Processa blocos @if(logo_url)
        $header = preg_replace_callback('/@if\(logo_url\)([\s\S]*?)@else([\s\S]*?)@endif/', function($matches) use ($logoUrl, $logoWidth, $logoHeight, $data) {
            return $this->processLogoContent($matches[1], $logoUrl, $logoWidth, $logoHeight, $data);
        }, $header);

        $header = preg_replace_callback('/@if\(logo_url\)([\s\S]*?)@endif/', function($matches) use ($logoUrl, $logoWidth, $logoHeight, $data) {
            return $this->processLogoContent($matches[1], $logoUrl, $logoWidth, $logoHeight, $data);
        }, $header);

        // Substitui placeholders simples
        $header = $this->replaceLogoPlaceholders($header, $logoUrl, $logoWidth, $logoHeight, $data);

        return $header;
    }

    protected function processLogoContent(string $content, string $logoUrl, int $logoWidth, string $logoHeight, array $data): string
    {
        // Substitui [url_do_logo] e {{logo_url}} dentro de src="..." e atualiza o style
        $content = preg_replace_callback('/<img([^>]*)\s+src\s*=\s*["\'](\[url_do_logo\]|\{\{logo_url\}\})["\']([^>]*)>/i', function($matches) use ($logoUrl, $logoWidth, $logoHeight) {
            $beforeSrc = $matches[1];
            $afterSrc = $matches[3];
            
            // Novo src com URL
            $newSrc = 'src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '"';
            
            // Atualiza ou adiciona style com largura e altura
            $combinedAttrs = trim($beforeSrc . ' ' . $afterSrc);
            $styleAttr = '';
            
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $combinedAttrs, $styleMatch)) {
                $existingStyle = $styleMatch[1];
                // Remove max-width e height se existirem
                $existingStyle = preg_replace('/max-width\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = preg_replace('/height\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = trim($existingStyle, '; ');
                $styleAttr = 'style="' . htmlspecialchars($existingStyle, ENT_QUOTES);
                if ($existingStyle) $styleAttr .= '; ';
                $styleAttr .= 'max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            } else {
                $styleAttr = 'style="max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            }
            
            // Remove style antigo se existir
            $beforeSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $beforeSrc);
            $afterSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $afterSrc);
            
            return '<img' . trim($beforeSrc) . ' ' . $newSrc . ' ' . $styleAttr . ' ' . trim($afterSrc) . '>';
        }, $content);

        // Substitui outros placeholders
        $content = $this->replaceLogoPlaceholders($content, $logoUrl, $logoWidth, $logoHeight, $data);

        return $content;
    }

    protected function replaceLogoPlaceholders(string $content, string $logoUrl, int $logoWidth, string $logoHeight, array $data): string
    {
        $logoHtml = '<img src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '" alt="' . htmlspecialchars($data['app_name'] ?? 'Logo', ENT_QUOTES) . '" style="max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;" />';

        // Substitui [url_do_logo] dentro de src (já foi processado acima, mas fazemos fallback)
        // Agora também atualiza o style
        $content = preg_replace_callback('/<img([^>]*)\s+src\s*=\s*["\'](\[url_do_logo\]|\{\{logo_url\}\})["\']([^>]*)>/i', function($matches) use ($logoUrl, $logoWidth, $logoHeight) {
            $beforeSrc = $matches[1];
            $afterSrc = $matches[3];
            
            // Novo src com URL
            $newSrc = 'src="' . htmlspecialchars($logoUrl, ENT_QUOTES) . '"';
            
            // Atualiza ou adiciona style com largura e altura
            $combinedAttrs = trim($beforeSrc . ' ' . $afterSrc);
            $styleAttr = '';
            
            if (preg_match('/style\s*=\s*["\']([^"\']*)["\']/i', $combinedAttrs, $styleMatch)) {
                $existingStyle = $styleMatch[1];
                // Remove max-width e height se existirem
                $existingStyle = preg_replace('/max-width\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = preg_replace('/height\s*:\s*[^;]+;?/i', '', $existingStyle);
                $existingStyle = trim($existingStyle, '; ');
                $styleAttr = 'style="' . htmlspecialchars($existingStyle, ENT_QUOTES);
                if ($existingStyle) $styleAttr .= '; ';
                $styleAttr .= 'max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            } else {
                $styleAttr = 'style="max-width: ' . $logoWidth . 'px; height: ' . $logoHeight . '; display: block; margin-left: auto; margin-right: auto; margin-bottom: 10px;"';
            }
            
            // Remove style antigo se existir
            $beforeSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $beforeSrc);
            $afterSrc = preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $afterSrc);
            
            return '<img' . trim($beforeSrc) . ' ' . $newSrc . ' ' . $styleAttr . ' ' . trim($afterSrc) . '>';
        }, $content);

        // Substitui [url_do_logo] fora de src pela tag completa
        $content = str_replace('[url_do_logo]', $logoHtml, $content);
        $content = str_replace('{{logo_url}}', $logoHtml, $content);

        // Se tem logo e tem {{app_name}}, substitui por logo
        if (strpos($content, '{{app_name}}') !== false) {
            $content = str_replace('{{app_name}}', $logoHtml, $content);
        }

        return $content;
    }

    protected function ensureAbsoluteUrl(string $url): string
    {
        if (preg_match('/^https?:\/\//', $url)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            return 'http:' . $url;
        }

        return url($url);
    }
}

