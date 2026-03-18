<?php

namespace App\Services\Platform;

use App\Models\Platform\NotificationTemplate;
use App\Services\Tenant\TemplateRenderer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailTemplateTestSendService
{
    public function __construct(
        private readonly TemplateRenderer $renderer
    ) {
    }

    /**
     * @return array{subject:string,body:string}
     */
    public function renderTemplate(NotificationTemplate $template): array
    {
        $subjectTemplate = (string) ($template->subject ?? '');
        $bodyTemplate = (string) ($template->body ?? '');

        $placeholders = array_values(array_unique(array_merge(
            $this->renderer->extractPlaceholders($subjectTemplate),
            $this->renderer->extractPlaceholders($bodyTemplate)
        )));

        $context = $this->buildDummyContext($placeholders);
        $renderedSubject = $this->renderer->render($subjectTemplate, $context);
        $renderedBody = $this->renderer->render($bodyTemplate, $context);

        $renderedSubject = $this->replaceUnknownPlaceholdersWithDummy($renderedSubject);
        $renderedBody = $this->replaceUnknownPlaceholdersWithDummy($renderedBody);

        if (trim($renderedSubject) === '') {
            $renderedSubject = 'Teste de envio - ' . (string) ($template->display_name ?: $template->name);
        }

        if (trim($renderedBody) === '') {
            $renderedBody = 'Teste de envio do template ' . (string) ($template->display_name ?: $template->name) . '.';
        }

        return [
            'subject' => Str::limit(trim($renderedSubject), 255, ''),
            'body' => $renderedBody,
        ];
    }

    public function send(NotificationTemplate $template, string $destinationEmail): void
    {
        $rendered = $this->renderTemplate($template);
        $subject = $rendered['subject'];
        $htmlBody = $this->normalizeBodyForEmail($rendered['body']);

        Mail::send([], [], function ($message) use ($destinationEmail, $subject, $htmlBody): void {
            $message
                ->to($destinationEmail)
                ->subject($subject)
                ->html($htmlBody);
        });
    }

    /**
     * @param list<string> $placeholders
     */
    private function buildDummyContext(array $placeholders): array
    {
        $context = [];

        foreach ($placeholders as $placeholder) {
            data_set($context, $placeholder, $this->dummyValueFor($placeholder));
        }

        return $context;
    }

    private function replaceUnknownPlaceholdersWithDummy(string $content): string
    {
        $rendered = preg_replace_callback('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', function (array $matches): string {
            return $this->dummyValueFor((string) ($matches[1] ?? ''));
        }, $content);

        return $rendered ?? $content;
    }

    private function normalizeBodyForEmail(string $body): string
    {
        if ($this->looksLikeHtml($body)) {
            return $body;
        }

        return nl2br(e($body));
    }

    private function looksLikeHtml(string $content): bool
    {
        return preg_match('/<[^>]+>/', $content) === 1;
    }

    private function dummyValueFor(string $placeholder): string
    {
        $normalized = strtolower(trim($placeholder));
        $lastToken = strtolower((string) Str::of($normalized)->afterLast('.'));

        return match (true) {
            $lastToken === 'email' => 'teste@example.com',
            str_contains($lastToken, 'name') || $lastToken === 'nome' => 'Nome de Teste',
            str_contains($lastToken, 'date') || str_contains($lastToken, 'data') => now()->format('d/m/Y'),
            str_contains($lastToken, 'time') || str_contains($lastToken, 'hora') => now()->format('H:i'),
            str_contains($lastToken, 'phone') || str_contains($lastToken, 'telefone') => '5511999999999',
            str_contains($lastToken, 'link') || str_contains($lastToken, 'url') => 'https://example.com/teste',
            str_contains($lastToken, 'amount') || str_contains($lastToken, 'valor') => '199,90',
            str_contains($lastToken, 'code') || str_contains($lastToken, 'codigo') => '123456',
            str_contains($lastToken, 'minutes') || str_contains($lastToken, 'minutos') => '10',
            default => 'valor_teste_' . str_replace(['.', '-'], '_', $normalized),
        };
    }
}

