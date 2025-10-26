<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajuste se quiser restringir a usuários específicos
    }

    public function rules(): array
    {
        $invoiceId = $this->route('invoice'); // usado no update

        return [
            'subscription_id' => ['required', 'uuid', 'exists:subscriptions,id'],
            'tenant_id'       => ['required', 'uuid', 'exists:tenants,id'],
            'amount_cents'    => ['required', 'numeric', 'min:0'],
            'due_date'        => ['required', 'date', 'after_or_equal:today'],
            'status'          => ['required', 'in:pending,paid,overdue,canceled,refunded'],
            'payment_link'    => ['nullable', 'url', 'max:255'],
            'provider'        => ['nullable', 'string', 'max:50'],
            'provider_id'     => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_id.required' => 'Selecione uma assinatura válida.',
            'subscription_id.exists'   => 'A assinatura selecionada não existe.',
            'tenant_id.required'       => 'O tenant é obrigatório.',
            'tenant_id.exists'         => 'O tenant selecionado é inválido.',
            'amount_cents.required'    => 'O valor da fatura é obrigatório.',
            'amount_cents.numeric'     => 'O valor deve ser numérico.',
            'due_date.required'        => 'A data de vencimento é obrigatória.',
            'due_date.after_or_equal'  => 'A data de vencimento não pode ser anterior a hoje.',
            'status.required'          => 'O status da fatura é obrigatório.',
            'status.in'                => 'O status informado é inválido.',
            'payment_link.url'         => 'O link de pagamento deve ser uma URL válida.',
        ];
    }
}
