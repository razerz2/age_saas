<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Platform\Tenant;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormResponse;
use App\Models\Tenant\ResponseAnswer;
use App\Models\Tenant\Appointment;
use App\Http\Requests\Tenant\Responses\StoreFormResponseRequest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PublicFormController extends Controller
{
    /**
     * Exibe o formulário para o paciente responder
     */
    public function create(Request $request, $tenant, $form)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        // Busca o formulário
        $formModel = Form::findOrFail($form);
        $formModel->load(['sections.questions.options', 'questions.options']);

        // Verifica se há appointment_id na query string
        $appointmentId = $request->query('appointment');
        $appointment = null;
        
        if ($appointmentId) {
            $appointment = Appointment::find($appointmentId);
            
            // Valida que o appointment existe e está relacionado ao formulário
            if ($appointment) {
                // Verifica se o formulário corresponde ao agendamento
                $formForAppointment = Form::getFormForAppointment($appointment);
                if (!$formForAppointment || $formForAppointment->id !== $formModel->id) {
                    $appointment = null; // Formulário não corresponde ao agendamento
                }
            }
        }

        return view('tenant.public.form-response-create', [
            'tenant' => $tenantModel,
            'form' => $formModel,
            'appointment' => $appointment
        ]);
    }

    /**
     * Salva a resposta do formulário
     */
    public function store(StoreFormResponseRequest $request, $tenant, $form)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        // Garante que estamos no contexto do tenant
        $tenantModel->makeCurrent();

        $formModel = Form::findOrFail($form);
        $data = $request->validated();

        // Cria a resposta
        // Garante que appointment_id seja salvo se vier do request
        $appointmentId = $request->input('appointment_id') ?? $data['appointment_id'] ?? null;
        
        $response = FormResponse::create([
            'id' => Str::uuid(),
            'form_id' => $formModel->id,
            'patient_id' => $data['patient_id'],
            'appointment_id' => $appointmentId,
            'submitted_at' => $data['submitted_at'] ?? now(),
            'status' => $data['status'] ?? 'submitted',
        ]);

        // Salva as respostas
        if (!empty($data['answers'])) {
            foreach ($data['answers'] as $questionId => $value) {
                $this->saveAnswer($response->id, $questionId, $value);
            }
        }

        return redirect()->route('public.form.response.success', [
            'slug' => $tenantModel->subdomain,
            'form' => $form,
            'response' => $response->id
        ])->with('success', 'Formulário respondido com sucesso!');
    }

    /**
     * Página de sucesso após responder formulário
     */
    public function success(Request $request, $tenant, $form, $response)
    {
        $tenantModel = Tenant::where('subdomain', $tenant)->first();

        if (!$tenantModel) {
            abort(404, 'Clínica não encontrada.');
        }

        $tenantModel->makeCurrent();

        $responseModel = FormResponse::findOrFail($response);

        return view('tenant.public.form-response-success', [
            'tenant' => $tenantModel,
            'response' => $responseModel
        ]);
    }

    /**
     * Método auxiliar para salvar respostas
     */
    private function saveAnswer(string $responseId, string $questionId, $value)
    {
        $answer = ResponseAnswer::firstOrNew([
            'response_id' => $responseId,
            'question_id' => $questionId,
        ]);

        // Se for array (multi_choice), converte para JSON string
        if (is_array($value)) {
            $answer->value_text = json_encode($value);
            $answer->value_number = null;
            $answer->value_boolean = null;
            $answer->value_date = null;
        }
        // Se for string
        elseif (is_string($value)) {
            // Verifica se é uma data no formato YYYY-MM-DD
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                $answer->value_date = $value;
                $answer->value_text = null;
            } else {
                $answer->value_text = $value;
                $answer->value_date = null;
            }
            $answer->value_number = null;
            $answer->value_boolean = null;
        }
        // Se for numérico
        elseif (is_numeric($value)) {
            $answer->value_number = $value;
            $answer->value_text = null;
            $answer->value_boolean = null;
            $answer->value_date = null;
        }
        // Se for boolean
        elseif (is_bool($value)) {
            $answer->value_boolean = $value;
            $answer->value_text = null;
            $answer->value_number = null;
            $answer->value_date = null;
        }
        // Se for string "1" ou "0" (boolean como string)
        elseif ($value === '1' || $value === '0' || $value === 1 || $value === 0) {
            $answer->value_boolean = (bool)$value;
            $answer->value_text = null;
            $answer->value_number = null;
            $answer->value_date = null;
        }
        // Caso padrão
        else {
            $answer->value_text = $value !== null ? (string)$value : null;
            $answer->value_number = null;
            $answer->value_boolean = null;
            $answer->value_date = null;
        }

        if (!$answer->id) {
            $answer->id = Str::uuid();
        }

        $answer->save();
    }
}

