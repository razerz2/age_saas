<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HasDoctorFilter;
use App\Models\Tenant\Form;
use App\Models\Tenant\FormSection;
use App\Models\Tenant\FormQuestion;
use App\Models\Tenant\QuestionOption;
use App\Models\Tenant\MedicalSpecialty;
use App\Models\Tenant\Doctor;

use App\Http\Requests\Tenant\Forms\StoreFormRequest;
use App\Http\Requests\Tenant\Forms\UpdateFormRequest;
use App\Http\Requests\Tenant\Forms\AddSectionRequest;
use App\Http\Requests\Tenant\Forms\UpdateSectionRequest;
use App\Http\Requests\Tenant\Forms\AddQuestionRequest;
use App\Http\Requests\Tenant\Forms\UpdateQuestionRequest;
use App\Http\Requests\Tenant\Forms\AddOptionRequest;
use App\Http\Requests\Tenant\Forms\UpdateOptionRequest;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class FormController extends Controller
{
    use HasDoctorFilter;
    /** ------------------------------
     *       LIST / CREATE / EDIT
     * ------------------------------ */

    public function index()
    {
        $query = Form::with(['specialty', 'doctor']);

        // Aplicar filtro de médico
        $this->applyDoctorFilter($query, 'doctor_id');

        $forms = $query->orderBy('name')->paginate(20);

        return view('tenant.forms.index', compact('forms'));
    }

    public function create()
    {
        $doctorsQuery = Doctor::with('user');

        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.forms.create', compact('doctors'));
    }

    public function getSpecialtiesByDoctor($slug, $doctorId)
    {
        $doctor = Doctor::with('specialties')->findOrFail($doctorId);
        $specialties = $doctor->specialties()->orderBy('name')->get();

        return response()->json($specialties);
    }

    public function store(StoreFormRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        Form::create($data);

        return redirect()->route('tenant.forms.index')
            ->with('success', 'Formulário criado com sucesso.');
    }

    public function show($slug, $id)
    {
        $form = Form::with(['specialty', 'doctor.user'])->findOrFail($id);
        $sectionsCount = $form->sections()->count();
        $questionsCount = $form->questions()->count();

        return view('tenant.forms.show', compact('form', 'sectionsCount', 'questionsCount'));
    }

    public function preview($slug, $id)
    {
        $form = Form::findOrFail($id);
        $form->load([
            'sections.questions.options',
            'specialty',
            'doctor.user'
        ]);

        return view('tenant.forms.preview', compact('form'));
    }

    public function builder($slug, $id)
    {
        $form = Form::findOrFail($id);
        $form->load([
            'sections.questions.options',
            'specialty',
            'doctor.user'
        ]);

        return view('tenant.forms.builder', compact('form'));
    }

    public function edit($slug, $id)
    {
        $form = Form::findOrFail($id);
        $form->load([
            'sections.questions.options',
            'specialty',
            'doctor.user',
            'doctor.specialties'
        ]);

        $doctorsQuery = Doctor::with('user');
        
        // Aplicar filtro de médico
        $this->applyDoctorFilter($doctorsQuery);

        $doctors = $doctorsQuery->orderBy('id')->get();

        return view('tenant.forms.edit', compact('form', 'doctors'));
    }

    public function update(UpdateFormRequest $request, $slug, $id)
    {
        $form = Form::findOrFail($id);
        $form->update($request->validated());

        return back()->with('success', 'Formulário atualizado com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $form = Form::findOrFail($id);
        $formName = $form->name;
        $form->delete();

        return redirect()->route('tenant.forms.index')
            ->with('success', "Formulário '{$formName}' removido com sucesso.");
    }

    public function clearContent($slug, $id)
    {
        $form = Form::findOrFail($id);
        
        // Deletar todas as perguntas do formulário (isso deleta automaticamente as opções via cascade)
        // Isso inclui perguntas com e sem seção
        $form->questions()->delete();
        
        // Deletar todas as seções (agora que não há mais perguntas relacionadas)
        $form->sections()->delete();

        return redirect()->route('tenant.forms.index')
            ->with('success', "Conteúdo do formulário '{$form->name}' removido com sucesso. O formulário foi mantido.");
    }



    /** ------------------------------
     *            SECTIONS
     * ------------------------------ */

    public function addSection(AddSectionRequest $request, $slug, $id)
    {
        $form = Form::findOrFail($id);
        $data = $request->validated();

        $section = FormSection::create([
            'id'      => Str::uuid(),
            'form_id' => $form->id,
            'title'   => $data['title'],
            'position' => $data['position'] ?? 0,
        ]);

        return response()->json(['section' => $section], 201);
    }

    public function updateSection(UpdateSectionRequest $request, $slug, $id)
    {
        $section = FormSection::findOrFail($id);
        $section->update($request->validated());

        return response()->json(['section' => $section]);
    }

    public function deleteSection($slug, $id)
    {
        $section = FormSection::findOrFail($id);
        $section->delete();

        return response()->json(['message' => 'Section removida com sucesso.']);
    }



    /** ------------------------------
     *            QUESTIONS
     * ------------------------------ */

    public function addQuestion(AddQuestionRequest $request, $slug, $id)
    {
        $form = Form::findOrFail($id);
        $data = $request->validated();

        // Garantir que section_id seja null se estiver vazio
        $sectionId = !empty($data['section_id']) ? $data['section_id'] : null;
        
        // Se section_id foi fornecido, verificar se a seção pertence ao formulário
        if ($sectionId) {
            $section = FormSection::where('id', $sectionId)
                ->where('form_id', $form->id)
                ->first();
            
            if (!$section) {
                return response()->json([
                    'message' => 'A seção selecionada não pertence a este formulário.'
                ], 422);
            }
        }

        $question = FormQuestion::create([
            'id'        => Str::uuid(),
            'form_id'   => $form->id,
            'section_id' => $sectionId,
            'label'     => $data['label'],
            'help_text' => $data['help_text'] ?? null,
            'type'      => $data['type'],
            'required'  => $data['required'] ?? false,
            'position'  => $data['position'] ?? 0,
        ]);

        return response()->json(['question' => $question], 201);
    }

    public function updateQuestion(UpdateQuestionRequest $request, $slug, $id)
    {
        $question = FormQuestion::findOrFail($id);
        $question->update($request->validated());

        return response()->json(['question' => $question]);
    }

    public function deleteQuestion($slug, $id)
    {
        $question = FormQuestion::findOrFail($id);
        $question->delete();

        return response()->json(['message' => 'Pergunta removida com sucesso.']);
    }



    /** ------------------------------
     *            OPTIONS
     * ------------------------------ */

    public function addOption(AddOptionRequest $request, $slug, $id)
    {
        $question = FormQuestion::findOrFail($id);
        $data = $request->validated();

        $option = QuestionOption::create([
            'id'          => Str::uuid(),
            'question_id' => $question->id,
            'label'       => $data['label'],
            'value'       => $data['value'],
            'position'    => $data['position'] ?? 0,
        ]);

        return response()->json(['option' => $option], 201);
    }

    public function updateOption(UpdateOptionRequest $request, $slug, $id)
    {
        $option = QuestionOption::findOrFail($id);
        $option->update($request->validated());

        return response()->json(['option' => $option]);
    }

    public function deleteOption($slug, $id)
    {
        $option = QuestionOption::findOrFail($id);
        $option->delete();

        return response()->json(['message' => 'Opção removida com sucesso.']);
    }
}
