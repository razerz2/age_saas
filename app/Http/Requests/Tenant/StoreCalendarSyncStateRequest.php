<?php

namespace App\Http\Requests\Tenant\CalendarSync;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarSyncStateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'appointment_id'   => ['required', 'exists:tenant.appointments,id'],
            'external_event_id' => ['nullable', 'string', 'max:255'],
            'provider'         => ['required', 'in:google,apple'],
            'last_sync_at'     => ['nullable', 'date'],
        ];
    }

    /**
     * Personaliza as mensagens de erro de validação.
     */
    public function messages()
    {
        return [
            'appointment_id.required' => 'O agendamento é obrigatório.',
            'appointment_id.exists' => 'O agendamento selecionado não existe.',

            'external_event_id.string' => 'O ID do evento externo deve ser uma string válida.',
            'external_event_id.max' => 'O ID do evento externo não pode ter mais que 255 caracteres.',

            'provider.required' => 'O provedor é obrigatório.',
            'provider.in' => 'O provedor deve ser "google" ou "apple".',

            'last_sync_at.date' => 'A data da última sincronização deve ser uma data válida.',
        ];
    }
}
