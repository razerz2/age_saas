<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\Concerns\HandlesGridRequests;
use App\Models\Tenant\MedicalSpecialty;
use App\Http\Requests\Tenant\StoreMedicalSpecialtyRequest;
use App\Http\Requests\Tenant\UpdateMedicalSpecialtyRequest;
use Illuminate\Http\Request;

class MedicalSpecialtyController extends Controller
{
    use HandlesGridRequests;

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
        $data = $this->normalizeLabelOverrides($request->validated());

        MedicalSpecialty::create([
            'id'   => \Str::uuid(),
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'label_singular' => $data['label_singular'] ?? null,
            'label_plural' => $data['label_plural'] ?? null,
            'registration_label' => $data['registration_label'] ?? null,
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
        $specialty->update($this->normalizeLabelOverrides($request->validated()));

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
        $page = $this->gridPage($request);
        $perPage = $this->gridPerPage($request);

        $query = MedicalSpecialty::query();

        // Busca simples em nome e código
        $term = $this->gridSearch($request);
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        $sort = $this->gridSort($request, [
            'name' => 'name',
            'code' => 'code',
        ], 'name', 'asc');

        $query->orderBy($sort['column'], $sort['direction']);
        if ($sort['column'] !== 'name') {
            $query->orderBy('name', 'asc');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

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
            'meta' => $this->gridMeta($paginator),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeLabelOverrides(array $data): array
    {
        foreach (['label_singular', 'label_plural', 'registration_label'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $value = trim((string) $data[$key]);
            $data[$key] = $value !== '' ? $value : null;
        }

        return $data;
    }
}
