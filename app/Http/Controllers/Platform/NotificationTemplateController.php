<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Platform\NotificationTemplate;
use App\Services\TemplateRenderer;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationTemplateController extends Controller
{
    protected TemplateRenderer $renderer;
    protected WhatsAppService $whatsapp;

    public function __construct(TemplateRenderer $renderer, WhatsAppService $whatsapp)
    {
        $this->renderer = $renderer;
        $this->whatsapp = $whatsapp;
    }

    /**
     * Lista todos os templates
     */
    public function index()
    {
        $templates = NotificationTemplate::orderBy('display_name')->get();
        return view('platform.notification_templates.index', compact('templates'));
    }

    /**
     * Exibe formulário de edição
     */
    public function edit(NotificationTemplate $notificationTemplate)
    {
        return view('platform.notification_templates.edit', [
            'template' => $notificationTemplate
        ]);
    }

    /**
     * Atualiza template
     */
    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $rules = [
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ];

        // Subject só é obrigatório para email
        if ($notificationTemplate->channel === 'email') {
            $rules['subject'] = 'required|string|max:255';
        }

        $request->validate($rules);

        $data = [
            'body' => $request->body,
        ];

        if ($notificationTemplate->channel === 'email') {
            $data['subject'] = $request->subject;
        }

        $notificationTemplate->update($data);

        return redirect()
            ->route('Platform.notification-templates.index')
            ->with('success', 'Template atualizado com sucesso!');
    }

    /**
     * Restaura valores padrão
     */
    public function restore(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->update([
            'subject' => $notificationTemplate->default_subject,
            'body' => $notificationTemplate->default_body,
        ]);

        return redirect()
            ->route('Platform.notification-templates.index')
            ->with('success', 'Template restaurado para os valores padrão!');
    }

    /**
     * Envia teste de email ou whatsapp
     */
    public function testSend(Request $request, NotificationTemplate $notificationTemplate)
    {
        $request->validate([
            'email' => 'required_if:channel,email|email',
            'phone' => 'required_if:channel,whatsapp|string',
        ]);

        try {
            // Dados de exemplo para renderização
            $sampleData = $this->getSampleData($notificationTemplate->name);

            $rendered = $this->renderer->render($notificationTemplate->name, $sampleData);

            if ($notificationTemplate->channel === 'email') {
                Mail::send([], [], function ($message) use ($request, $rendered) {
                    $message->to($request->email)
                        ->subject($rendered->subject ?? 'Teste de Template')
                        ->setBody($rendered->body, 'text/html');
                });

                return response()->json([
                    'success' => true,
                    'message' => 'Email de teste enviado com sucesso!'
                ]);
            } else {
                $success = $this->whatsapp->sendMessage($request->phone, $rendered->body);

                return response()->json([
                    'success' => $success,
                    'message' => $success 
                        ? 'Mensagem WhatsApp enviada com sucesso!' 
                        : 'Falha ao enviar mensagem WhatsApp.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erro ao enviar teste de template', [
                'template' => $notificationTemplate->name,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao enviar teste: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle enabled/disabled via AJAX
     */
    public function toggle(Request $request, NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->update([
            'enabled' => !$notificationTemplate->enabled
        ]);

        return response()->json([
            'success' => true,
            'enabled' => $notificationTemplate->enabled,
            'message' => $notificationTemplate->enabled 
                ? 'Template ativado!' 
                : 'Template desativado!'
        ]);
    }

    /**
     * Retorna dados de exemplo para cada template
     */
    protected function getSampleData(string $templateName): array
    {
        $samples = [
            'subscription_created' => [
                'tenant_name' => 'Clínica Exemplo',
                'plan_name' => 'Plano Premium',
                'plan_value' => '299,90',
            ],
            'subscription_renewed' => [
                'tenant_name' => 'Clínica Exemplo',
                'next_due_date' => now()->addMonth()->format('d/m/Y'),
            ],
            'invoice_created' => [
                'tenant_name' => 'Clínica Exemplo',
                'invoice_value' => '299,90',
                'due_date' => now()->addDays(7)->format('d/m/Y'),
                'payment_url' => 'https://exemplo.com/pagar/123',
            ],
            'invoice_paid' => [
                'tenant_name' => 'Clínica Exemplo',
                'invoice_id' => '12345',
                'invoice_value' => '299,90',
                'payment_date' => now()->format('d/m/Y H:i'),
            ],
            'invoice_overdue' => [
                'tenant_name' => 'Clínica Exemplo',
                'invoice_value' => '299,90',
                'due_date' => now()->subDays(5)->format('d/m/Y'),
                'payment_url' => 'https://exemplo.com/pagar/123',
            ],
            'tenant_welcome' => [
                'tenant_name' => 'Clínica Exemplo',
                'email' => 'admin@exemplo.com',
                'password' => 'senha123',
                'login_url' => 'https://exemplo.com/login',
            ],
            'pre_tenant_created' => [
                'pre_tenant_name' => 'Clínica Nova',
            ],
            'pre_tenant_payment_confirmed' => [
                'pre_tenant_name' => 'Clínica Nova',
            ],
            'invoice_notification' => [
                'tenant_name' => 'Clínica Exemplo',
                'invoice_value' => '299,90',
                'due_date' => now()->addDays(7)->format('d/m/Y'),
                'payment_url' => 'https://exemplo.com/pagar/123',
            ],
            'welcome_short' => [
                'tenant_name' => 'Clínica Exemplo',
                'email' => 'admin@exemplo.com',
                'password' => 'senha123',
                'login_url' => 'https://exemplo.com/login',
            ],
            'subscription_alert' => [
                'tenant_name' => 'Clínica Exemplo',
                'action' => 'foi renovada',
                'plan_name' => 'Plano Premium',
                'additional_info' => 'Próximo vencimento: ' . now()->addMonth()->format('d/m/Y'),
            ],
        ];

        return $samples[$templateName] ?? [];
    }
}
