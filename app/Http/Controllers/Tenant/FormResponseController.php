<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormResponse;
use App\Models\Tenant\ResponseAnswer;
use App\Models\Tenant\Patient;
use App\Models\Tenant\Appointment;

use App\Http\Requests\Tenant\Responses\StoreFormResponseRequest;
use App\Http\Requests\Tenant\Responses\UpdateFormResponseRequest;
use App\Http\Requests\Tenant\Responses\StoreResponseAnswerRequest;
use App\Http\Requests\Tenant\Responses\UpdateResponseAnswerRequest;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class FormResponseController extends Controller
{
    /** -----------------------------------------
     *            LISTAR RESPOSTAS
     * -----------------------------------------*/
    public function index()
    {
        $responses = FormResponse::with(['form', 'patient'])
            ->orderBy('submitted_at', 'desc')
            ->paginate(20);

        return view('tenant.responses.index', compact('responses'));
    }


    /** -----------------------------------------
     *      INICIAR PREENCHIMENTO DE FORMULÁRIO
     * -----------------------------------------*/
    public function create(Form $form)
    {
        $form->load(['sections.questions.options']);
        $patients = Patient::orderBy('full_name')->get();

        return view('tenant.responses.create', compact('form', 'patients'));
    }


    /** -----------------------------------------
     *           SALVAR RESPOSTA (STORE)
     * -----------------------------------------*/
    public function store(StoreFormResponseRequest $request, Form $form)
    {
        $data = $request->validated();

        $response = FormResponse::create([
            'id'           => Str::uuid(),
            'form_id'      => $form->id,
            'patient_id'   => $data['patient_id'],
            'appointment_id' => $data['appointment_id'] ?? null,
            'submitted_at' => $data['submitted_at'] ?? now(),
            'status'       => $data['status'] ?? 'submitted',
        ]);

        // Salvar respostas
        if (!empty($data['answers'])) {
            foreach ($data['answers'] as $questionId => $value) {
                $this->saveAnswer($response->id, $questionId, $value);
            }
        }

        return redirect()->route('tenant.responses.index')
            ->with('success', 'Formulário respondido com sucesso.');
    }


    /** -----------------------------------------
     *        VISUALIZAR FORMULÁRIO RESPONDIDO
     * -----------------------------------------*/
    public function show(FormResponse $response)
    {
        $response->load([
            'form.sections.questions.options',
            'answers',
            'patient',
            'appointment'
        ]);

        return view('tenant.responses.show', compact('response'));
    }


    /** -----------------------------------------
     *           EDITAR RESPOSTA (PENDING)
     * -----------------------------------------*/
    public function edit(FormResponse $response)
    {
        $response->load([
            'form.sections.questions.options',
            'answers',
            'patient',
            'appointment'
        ]);

        return view('tenant.responses.edit', compact('response'));
    }


    /** -----------------------------------------
     *                UPDATE
     * -----------------------------------------*/
    public function update(UpdateFormResponseRequest $request, FormResponse $response)
    {
        $data = $request->validated();

        $response->update([
            'submitted_at' => $data['submitted_at'] ?? now(),
            'status'       => $data['status'],
        ]);

        // Atualizar respostas
        if (!empty($data['answers'])) {
            foreach ($data['answers'] as $questionId => $value) {
                $this->saveAnswer($response->id, $questionId, $value);
            }
        }

        return redirect()->route('tenant.responses.show', $response)
            ->with('success', 'Resposta atualizada com sucesso.');
    }


    /** -----------------------------------------
     *                REMOVE RESPOSTA
     * -----------------------------------------*/
    public function destroy(FormResponse $response)
    {
        $response->answers()->delete();
        $response->delete();

        return redirect()->route('tenant.responses.index')
            ->with('success', 'Resposta removida.');
    }


    /** -----------------------------------------
     *       MÉTODO AUXILIAR PARA SALVAR RESPOSTAS
     * -----------------------------------------*/
    private function saveAnswer(string $responseId, string $questionId, $value)
    {
        $answer = ResponseAnswer::firstOrNew([
            'response_id' => $responseId,
            'question_id' => $questionId,
        ]);

        $answer->value_text    = is_string($value) ? $value : null;
        $answer->value_number  = is_numeric($value) ? $value : null;
        $answer->value_boolean = is_bool($value) ? $value : null;
        $answer->value_date    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;

        $answer->save();
    }


    /** -----------------------------------------
     *    ENDPOINT OPCIONAL PARA SALVAR 1 RESPOSTA VIA AJAX
     * -----------------------------------------*/
    public function storeAnswer(StoreResponseAnswerRequest $request, FormResponse $response)
    {
        $this->saveAnswer(
            $response->id,
            $request->question_id,
            $request->value
        );

        return response()->json(['success' => true]);
    }


    /** -----------------------------------------
     *      ENDPOINT OPCIONAL UPDATE VIA AJAX
     * -----------------------------------------*/
    public function updateAnswer(UpdateResponseAnswerRequest $request, ResponseAnswer $answer)
    {
        $value = $request->value;

        $answer->value_text    = is_string($value) ? $value : null;
        $answer->value_number  = is_numeric($value) ? $value : null;
        $answer->value_boolean = is_bool($value) ? $value : null;
        $answer->value_date    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;

        $answer->save();

        return response()->json(['success' => true]);
    }
}
