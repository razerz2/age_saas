<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
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

class FormController extends Controller
{
    /** ------------------------------
     *       LIST / CREATE / EDIT
     * ------------------------------ */

    public function index()
    {
        $forms = Form::with(['specialty', 'doctor'])
            ->orderBy('name')
            ->paginate(20);

        return view('tenant.forms.index', compact('forms'));
    }

    public function create()
    {
        $specialties = MedicalSpecialty::orderBy('name')->get();
        $doctors = Doctor::with('user')->orderBy('id')->get();

        return view('tenant.forms.create', compact('specialties', 'doctors'));
    }

    public function store(StoreFormRequest $request)
    {
        $data = $request->validated();
        $data['id'] = Str::uuid();

        Form::create($data);

        return redirect()->route('tenant.forms.index')
            ->with('success', 'Formulário criado com sucesso.');
    }

    public function edit(Form $form)
    {
        $form->load([
            'sections.questions.options',
            'specialty',
            'doctor.user'
        ]);

        $specialties = MedicalSpecialty::orderBy('name')->get();
        $doctors = Doctor::with('user')->orderBy('id')->get();

        return view('tenant.forms.edit', compact('form', 'specialties', 'doctors'));
    }

    public function update(UpdateFormRequest $request, Form $form)
    {
        $form->update($request->validated());

        return back()->with('success', 'Formulário atualizado com sucesso.');
    }

    public function destroy(Form $form)
    {
        $form->delete();

        return redirect()->route('tenant.forms.index')
            ->with('success', 'Formulário removido.');
    }



    /** ------------------------------
     *            SECTIONS
     * ------------------------------ */

    public function addSection(AddSectionRequest $request, Form $form)
    {
        $data = $request->validated();

        $section = FormSection::create([
            'id'      => Str::uuid(),
            'form_id' => $form->id,
            'title'   => $data['title'],
            'position' => $data['position'] ?? 0,
        ]);

        return response()->json(['section' => $section], 201);
    }

    public function updateSection(UpdateSectionRequest $request, FormSection $section)
    {
        $section->update($request->validated());

        return response()->json(['section' => $section]);
    }

    public function deleteSection(FormSection $section)
    {
        $section->delete();

        return response()->json(['message' => 'Section removida com sucesso.']);
    }



    /** ------------------------------
     *            QUESTIONS
     * ------------------------------ */

    public function addQuestion(AddQuestionRequest $request, Form $form)
    {
        $data = $request->validated();

        $question = FormQuestion::create([
            'id'        => Str::uuid(),
            'form_id'   => $form->id,
            'section_id' => $data['section_id'] ?? null,
            'label'     => $data['label'],
            'help_text' => $data['help_text'] ?? null,
            'type'      => $data['type'],
            'required'  => $data['required'] ?? false,
            'position'  => $data['position'] ?? 0,
        ]);

        return response()->json(['question' => $question], 201);
    }

    public function updateQuestion(UpdateQuestionRequest $request, FormQuestion $question)
    {
        $question->update($request->validated());

        return response()->json(['question' => $question]);
    }

    public function deleteQuestion(FormQuestion $question)
    {
        $question->delete();

        return response()->json(['message' => 'Pergunta removida com sucesso.']);
    }



    /** ------------------------------
     *            OPTIONS
     * ------------------------------ */

    public function addOption(AddOptionRequest $request, FormQuestion $question)
    {
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

    public function updateOption(UpdateOptionRequest $request, QuestionOption $option)
    {
        $option->update($request->validated());

        return response()->json(['option' => $option]);
    }

    public function deleteOption(QuestionOption $option)
    {
        $option->delete();

        return response()->json(['message' => 'Opção removida com sucesso.']);
    }
}
