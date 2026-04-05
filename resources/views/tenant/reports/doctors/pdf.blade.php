<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Médicos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        .meta { margin-bottom: 12px; color: #4b5563; }
        .filters { margin-bottom: 12px; }
        .filters li { margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
        .warn { margin-top: 10px; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Relatório de Médicos</h1>
    <div class="meta">Gerado em: {{ $generatedAt->format('d/m/Y H:i') }}</div>

    @if(!empty($activeFilters))
        <ul class="filters">
            @foreach($activeFilters as $key => $value)
                <li><strong>{{ $key }}:</strong> {{ is_array($value) ? json_encode($value) : $value }}</li>
            @endforeach
        </ul>
    @endif

    <table>
        <thead>
            <tr>
                <th>Nome</th>
                <th>Especialidades</th>
                <th>Status</th>
                <th>Agendamentos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->doctor_name ?? ($row->user->name ?? 'N/A') }}</td>
                    <td>{{ $row->specialties->pluck('name')->filter()->join(', ') ?: 'N/A' }}</td>
                    <td>{{ ($row->doctor_status ?? '') === 'active' ? 'Ativo' : 'Inativo' }}</td>
                    <td>{{ (int) ($row->appointments_count ?? 0) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Nenhum registro encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($truncated)
        <p class="warn">Exportação limitada aos primeiros {{ $pdfMaxRows }} registros para manter performance do PDF.</p>
    @endif
</body>
</html>
