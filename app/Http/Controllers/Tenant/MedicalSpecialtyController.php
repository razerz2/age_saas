<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MedicalSpecialty;
use App\Http\Requests\Tenant\StoreMedicalSpecialtyRequest;
use App\Http\Requests\Tenant\UpdateMedicalSpecialtyRequest;
use Illuminate\Http\Request;

class MedicalSpecialtyController extends Controller
{
    public function index()
    {
        return view('tenant.specialties.index');
    }

    public function create()
    {
        return view('tenant.specialties.create');
    }

    public function store(StoreMedicalSpecialtyRequest $request)
    {
        MedicalSpecialty::create([
            'id'   => \Str::uuid(),
            'name' => $request->validated()['name'],
            'code' => $request->validated()['code'] ?? null,
        ]);

        return redirect()->route('tenant.specialties.index', ['slug' => tenant()->subdomain])
            ->with('success', 'Especialidade cadastrada com sucesso.');
    }

    public function show($slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        return view('tenant.specialties.show', compact('specialty'));
    }

    public function edit($slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        return view('tenant.specialties.edit', compact('specialty'));
    }

    public function update(UpdateMedicalSpecialtyRequest $request, $slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        $specialty->update($request->validated());

        return redirect()->route('tenant.specialties.index', ['slug' => $slug])
            ->with('success', 'Especialidade atualizada com sucesso.');
    }

    public function destroy($slug, $id)
    {
        $specialty = MedicalSpecialty::findOrFail($id);
        $specialty->delete();

        return redirect()->route('tenant.specialties.index', ['slug' => $slug])
            ->with('success', 'Especialidade removida.');
    }

    /**
     * Retorna dados para Grid.js na tela de especialidades.
     */
    public function gridData(Request $request, $slug)
    {
        $page = max(1, (int) $request->input('page', 1));
        $limit = max(1, min(100, (int) $request->input('limit', 10)));

        $query = MedicalSpecialty::query();

        // Busca simples em nome e código
        $search = $request->input('search');
        if (is_array($search) && !empty($search['value'])) {
            $term = trim($search['value']);

            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        // Ordenação básica
        $sort = $request->input('sort');
        if (is_array($sort) && isset($sort['column'], $sort['direction'])) {
            $column = $sort['column'];
            $direction = strtolower($sort['direction']) === 'asc' ? 'asc' : 'desc';

            $sortable = [
                'name' => 'name',
                'code' => 'code',
            ];

            if (isset($sortable[$column])) {
                $query->orderBy($sortable[$column], $direction);
            } else {
                $query->orderBy('name');
            }
        } else {
            $query->orderBy('name');
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        $data = [];

        foreach ($paginator->items() as $specialty) {
            $actions = view('tenant.specialties.partials.actions', [
                'specialty' => $specialty,
            ])->render();

            $data[] = [
                'name'    => e($specialty->name),
                'code'    => e($specialty->code ?? '-'),
                'actions' => $actions,
            ];
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }
}
