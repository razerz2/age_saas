<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationOutboxRequest extends FormRequest
{
    public function authorize(): bool
    {
        // ajuste se quiser permitir apenas usuários autenticados/admins
        return true;
    }

    public function rules(): array
    {
        $notificationId = $this->route('notification_outbox'); // usado no update

        return [
            'tenant_id'    => ['required', 'uuid', 'exists:tenants,id'],
            'channel'      => ['required', 'in:email,sms,push,whatsapp,in_app'],
            'subject'      => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'meta'         => ['nullable', 'array'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
            'sent_at'      => ['nullable', 'date'],
            'status'       => ['required', 'in:pending,sent,failed,canceled'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Selecione um tenant válido.',
            'tenant_id.exists'   => 'O tenant informado não existe.',
            'channel.required'   => 'O canal de envio é obrigatório.',
            'channel.in'         => 'O canal informado é inválido.',
            'subject.required'   => 'O assunto é obrigatório.',
            'body.required'      => 'O corpo da mensagem é obrigatório.',
            'scheduled_at.after_or_equal' => 'A data de agendamento deve ser no futuro ou no presente.',
            'status.required'    => 'O status é obrigatório.',
            'status.in'          => 'O status informado é inválido.',
        ];
    }
}
