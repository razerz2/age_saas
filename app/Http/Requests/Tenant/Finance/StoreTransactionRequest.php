<?php

namespace App\Http\Requests\Tenant\Finance;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'in:income,expense'],
            'description' => ['required', 'string', 'max:500'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'date' => ['required', 'date'],
            'status' => ['nullable', 'in:pending,paid,cancelled'],
            'account_id' => ['nullable', 'uuid', 'exists:financial_accounts,id'],
            'category_id' => ['nullable', 'uuid', 'exists:financial_categories,id'],
            'appointment_id' => ['nullable', 'uuid', 'exists:appointments,id'],
            'patient_id' => ['nullable', 'uuid', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'uuid', 'exists:doctors,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O tipo da transação é obrigatório.',
            'type.in' => 'O tipo deve ser: receita ou despesa.',
            'description.required' => 'A descrição é obrigatória.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'date.required' => 'A data é obrigatória.',
            'date.date' => 'A data deve ser uma data válida.',
            'status.in' => 'O status deve ser: pendente, pago ou cancelado.',
            'account_id.exists' => 'A conta selecionada não existe.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'appointment_id.exists' => 'O agendamento selecionado não existe.',
            'patient_id.exists' => 'O paciente selecionado não existe.',
            'doctor_id.exists' => 'O médico selecionado não existe.',
        ];
    }
}

