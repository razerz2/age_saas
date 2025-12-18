@extends('layouts.freedash.main')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">🚀 Importação em Andamento</h5>
                </div>
                <div class="card-body">
                    <div id="import-container">
                        <div class="text-center mb-4">
                            <h4 id="status-text" class="text-primary fw-bold">Processando arquivo...</h4>
                            <p class="text-muted" id="file-info">Arquivo: {{ $importLog->file_name }}</p>
                        </div>

                        <div class="progress mb-3" style="height: 30px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                                 role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                0%
                            </div>
                        </div>

                        <div class="row text-center mb-4">
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h3 id="total-rows">0</h3>
                                    <small class="text-muted uppercase fw-bold">Total de Linhas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h3 id="processed-rows" class="text-success">0</h3>
                                    <small class="text-muted uppercase fw-bold">Processadas</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h3 id="skipped-count" class="text-warning">0</h3>
                                    <small class="text-muted uppercase fw-bold">Ignoradas (Já existem)</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-3 border rounded bg-light">
                                    <h3 id="error-count" class="text-danger">0</h3>
                                    <small class="text-muted uppercase fw-bold">Erros</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">Log de Eventos:</h6>
                            <div id="log-container" class="bg-dark text-light p-3 rounded" style="height: 300px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                                <div class="text-info">>> Iniciando processo de importação...</div>
                            </div>
                        </div>

                        <div id="finish-actions" class="mt-4 text-center d-none">
                            <hr>
                            <h5 class="text-success fw-bold mb-3">✅ Importação Concluída!</h5>
                            <a href="{{ route('clinic-networks.index') }}" class="btn btn-primary px-4">Voltar para Redes</a>
                            <button onclick="window.location.reload()" class="btn btn-outline-secondary px-4">Ver Detalhes do Log</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const importId = "{{ $importLog->id }}";
    const statusUrl = "{{ route('import.status', $importLog->id) }}";
    let lastProcessedCount = 0;
    let lastSkippedCount = 0;

    function updateStatus() {
        fetch(statusUrl)
            .then(response => response.json())
            .then(data => {
                // Atualizar contadores
                document.getElementById('total-rows').innerText = data.total;
                document.getElementById('processed-rows').innerText = data.processed;
                document.getElementById('error-count').innerText = data.errors;
                document.getElementById('skipped-count').innerText = data.skipped;
                
                // Atualizar barra de progresso
                const percentage = data.percentage + '%';
                const progressBar = document.getElementById('progress-bar');
                progressBar.style.width = percentage;
                progressBar.innerText = percentage;

                // Atualizar Log
                if (data.summary) {
                    const logContainer = document.getElementById('log-container');
                    
                    // Adicionar novos erros
                    if (data.summary.errors && data.summary.errors.length > 0) {
                        data.summary.errors.forEach((err, idx) => {
                            if (!document.getElementById(`err-${idx}`)) {
                                const div = document.createElement('div');
                                div.id = `err-${idx}`;
                                div.className = 'text-danger';
                                div.innerText = `>> ${err}`;
                                logContainer.appendChild(div);
                                logContainer.scrollTop = logContainer.scrollHeight;
                            }
                        });
                    }

                    // Adicionar novos ignorados
                    if (data.summary.skipped && data.summary.skipped.length > lastSkippedCount) {
                        for (let i = lastSkippedCount; i < data.summary.skipped.length; i++) {
                            const div = document.createElement('div');
                            div.className = 'text-warning';
                            div.innerText = `>> ${data.summary.skipped[i]}`;
                            logContainer.appendChild(div);
                            lastSkippedCount++;
                        }
                        logContainer.scrollTop = logContainer.scrollHeight;
                    }

                    // Adicionar novos sucessos (limitado para não travar a tela se forem muitos)
                    if (data.summary.success && data.summary.success.length > lastProcessedCount) {
                        for (let i = lastProcessedCount; i < data.summary.success.length; i++) {
                            const div = document.createElement('div');
                            div.className = 'text-success';
                            div.innerText = `>> ${data.summary.success[i]}`;
                            logContainer.appendChild(div);
                            lastProcessedCount++;
                        }
                        logContainer.scrollTop = logContainer.scrollHeight;
                    }
                }

                // Verificar se finalizou
                if (data.status === 'completed' || data.status === 'failed') {
                    document.getElementById('status-text').innerText = data.status === 'completed' ? 'Importação Concluída!' : 'Falha na Importação';
                    document.getElementById('status-text').className = data.status === 'completed' ? 'text-success fw-bold' : 'text-danger fw-bold';
                    document.getElementById('finish-actions').classList.remove('d-none');
                    progressBar.classList.remove('progress-bar-animated');
                    return; // Para o polling
                }

                // Continuar polling
                setTimeout(updateStatus, 3000);
            })
            .catch(error => {
                console.error('Erro ao buscar status:', error);
                setTimeout(updateStatus, 5000);
            });
    }

    // Iniciar polling
    document.addEventListener('DOMContentLoaded', updateStatus);
</script>
@endpush
@endsection

