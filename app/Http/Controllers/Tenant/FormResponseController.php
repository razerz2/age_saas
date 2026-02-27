<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class FormResponseController extends Controller
{
    use HasDoctorFilter;
    use HandlesGridRequests;
    /** -----------------------------------------
     *            LISTAR RESPOSTAS
     * -----------------------------------------*/
    public function index()
    {
        $query = FormResponse::with(['form.doctor', 'patient']);

        // Aplicar filtro de médico através do relacionamento form
        $this->applyDoctorFilterWhereHas($query, 'form', 'doctor_id');

        $responses = $query->orderBy('submitted_at', 'desc')->paginate(20);

        return view('tenant.form_responses.index');
    }


    public function gridData(Request $request, $slug)
    {
        $query = FormResponse::with([
            'form',
            'patient',
            'appointment'
        ]);

        // Aplicar filtro de médico através do relacionamento form.
        $this->applyDoctorFilterWhereHas($query, 'form', 'doctor_id');

        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        // Busca global
        $search = $this->gridSearch($request);
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->whereHas('form', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('patient', function ($sub) use ($search) {
                    $sub->where('full_name', 'like', "%{$search}%");
                });
            });
        }

        // Ordenação whitelist
        $sortable = [
            'form'         => 'form_id',
            'patient'      => 'patient_id',
            'appointment'  => 'appointment_id',
            'submitted_at' => 'submitted_at',
        ];

        $sort = $this->gridSort($request, $sortable, 'submitted_at', 'desc');
        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'submitted_at') {
            $query->orderBy('submitted_at', 'desc');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(function (FormResponse $response) {

            return [
                'form'        => e(optional($response->form)->name ?? '-'),
                'patient'     => e(optional($response->patient)->full_name ?? '-'),
                'appointment' => $response->appointment
                    ? $response->appointment->starts_at?->format('d/m/Y H:i')
                    : '-',
                'submitted_at' => optional($response->submitted_at)?->format('d/m/Y H:i'),
                'actions'     => view('tenant.form_responses.partials.actions', compact('response'))->render(),
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => $this->gridMeta($paginator),
        ]);
    }


    /** -----------------------------------------
     *      INICIAR PREENCHIMENTO DE FORMULÁRIO
     * -----------------------------------------*/
    public function create($slug, $id)
    {
        $form = Form::findOrFail($id);
        $form->load(['sections.questions.options']);
        $patients = Patient::orderBy('full_name')->get();

        return view('tenant.responses.create', compact('form', 'patients'));
    }


    /** -----------------------------------------
     *           SALVAR RESPOSTA (STORE)
     * -----------------------------------------*/
    public function store(StoreFormResponseRequest $request, $slug, $id)
    {
        $form = Form::findOrFail($id);
        $data = $request->validated();

        // Se houver appointment_id, verificar se já existe resposta para este agendamento e formulário
        if (!empty($data['appointment_id'])) {
            $existingResponse = FormResponse::where('appointment_id', $data['appointment_id'])
                ->where('form_id', $form->id)
                ->first();
            
            if ($existingResponse) {
                // Atualizar resposta existente em vez de criar nova
                $existingResponse->update([
                    'patient_id'   => $data['patient_id'],
                    'submitted_at' => $data['submitted_at'] ?? now(),
                    'status'       => $data['status'] ?? 'submitted',
                ]);

                // Remover respostas antigas
                $existingResponse->answers()->delete();

                // Salvar novas respostas
                if (!empty($data['answers'])) {
                    foreach ($data['answers'] as $questionId => $value) {
                        $this->saveAnswer($existingResponse->id, $questionId, $value);
                    }
                }

                return redirect()->route('tenant.responses.index', ['slug' => $slug])
                    ->with('success', 'Formulário atualizado com sucesso.');
            }
        }

        // Criar nova resposta apenas se não existir
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

        return redirect()->route('tenant.responses.index', ['slug' => $slug])
            ->with('success', 'Formulário respondido com sucesso.');
    }


    /** -----------------------------------------
     *        VISUALIZAR FORMULÁRIO RESPONDIDO
     * -----------------------------------------*/
    public function show($slug, $id)
    {
        $response = FormResponse::findOrFail($id);
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
    public function edit($slug, $id)
    {
        $response = FormResponse::findOrFail($id);
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
    public function update(UpdateFormResponseRequest $request, $slug, $id)
    {
        $response = FormResponse::findOrFail($id);
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

        return redirect()->route('tenant.responses.show', ['slug' => $slug, 'id' => $response->id])
            ->with('success', 'Resposta atualizada com sucesso.');
    }


    /** -----------------------------------------
     *                REMOVE RESPOSTA
     * -----------------------------------------*/
    public function destroy($slug, $id)
    {
        $response = FormResponse::findOrFail($id);
        
        // Verificar autorização
        $this->authorize('delete', $response);
        
        $response->answers()->delete();
        $response->delete();

        return redirect()->route('tenant.responses.index', ['slug' => $slug])
            ->with('success', 'Resposta removida com sucesso.');
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


    /** -----------------------------------------
     *    ENDPOINT OPCIONAL PARA SALVAR 1 RESPOSTA VIA AJAX
     * -----------------------------------------*/
    public function storeAnswer(StoreResponseAnswerRequest $request, $slug, $id)
    {
        $response = FormResponse::findOrFail($id);
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
    public function updateAnswer(UpdateResponseAnswerRequest $request, $id)
    {
        $answer = ResponseAnswer::findOrFail($id);
        $value = $request->value;

        $answer->value_text    = is_string($value) ? $value : null;
        $answer->value_number  = is_numeric($value) ? $value : null;
        $answer->value_boolean = is_bool($value) ? $value : null;
        $answer->value_date    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null;

        $answer->save();

        return response()->json(['success' => true]);
    }
}
