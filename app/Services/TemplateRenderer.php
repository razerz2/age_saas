<?php

namespace App\Services;

use App\Models\Platform\NotificationTemplate;
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

        return new RenderedMessageDTO(
            subject: $renderedSubject,
            body: $renderedBody
        );
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
}

