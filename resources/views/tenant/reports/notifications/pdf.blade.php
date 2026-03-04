<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatorio de Notificacoes</title>
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
    <h1>Relatorio de Notificacoes</h1>
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
                <th>Titulo</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Lida em</th>
                <th>Criada em</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>{{ $row->title ?? 'N/A' }}</td>
                    <td>{{ $row->type ?? '-' }}</td>
                    <td>{{ $row->read_at ? 'Lida' : 'Nao lida' }}</td>
                    <td>{{ $row->read_at ? $row->read_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $row->created_at ? $row->created_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Nenhum registro encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($truncated)
        <p class="warn">Exportacao limitada aos primeiros {{ $pdfMaxRows }} registros para manter performance do PDF.</p>
    @endif
</body>
</html>
