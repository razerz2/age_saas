<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatorio de Agendamentos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 16px; margin: 0 0 8px; }
        .meta { margin-bottom: 12px; color: #4b5563; }
        .filters { margin-bottom: 12px; }
        .filters li { margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 10px; }
        .warn { margin-top: 10px; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Relatorio de Agendamentos</h1>
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
                <th>Paciente</th>
                <th>Medico</th>
                <th>Especialidade</th>
                <th>Tipo</th>
                <th>Data</th>
                <th>Hora</th>
                <th>Modo</th>
                <th>Status</th>
                <th>Origem</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->patient_name ?? 'N/A' }}</td>
                    <td>{{ $row->doctor_name ?? 'N/A' }}</td>
                    <td>{{ $row->specialty_name ?? 'N/A' }}</td>
                    <td>{{ $row->appointment_type_name ?? 'N/A' }}</td>
                    <td>{{ $row->starts_at ? $row->starts_at->format('d/m/Y') : '-' }}</td>
                    <td>{{ $row->starts_at ? $row->starts_at->format('H:i') : '-' }}</td>
                    <td>{{ ($row->appointment_mode ?? 'presencial') === 'online' ? 'Online' : 'Presencial' }}</td>
                    <td>{{ $row->status_translated }}</td>
                    <td>{{ $row->origin ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Nenhum registro encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($truncated)
        <p class="warn">Exportacao limitada aos primeiros {{ $pdfMaxRows }} registros para manter performance do PDF.</p>
    @endif
</body>
</html>
