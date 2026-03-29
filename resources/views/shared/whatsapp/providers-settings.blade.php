@php
    $providerFieldName = $providerFieldName ?? 'WHATSAPP_PROVIDER';
    $providerValue = $providerValue ?? ($settings['WHATSAPP_PROVIDER'] ?? 'whatsapp_business');
    $includeEvolutionProvider = (bool) ($includeEvolutionProvider ?? false);
@endphp

<div class="mb-3">
    <label>Provedor WhatsApp</label>
    <select class="form-select w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="{{ $providerFieldName }}" id="whatsapp-provider-select">
        <option value="whatsapp_business" {{ $providerValue == 'whatsapp_business' ? 'selected' : '' }}>
            WhatsApp Business (Meta)
        </option>
        <option value="zapi" {{ $providerValue == 'zapi' ? 'selected' : '' }}>
            Z-API
        </option>
        <option value="waha" {{ $providerValue == 'waha' ? 'selected' : '' }}>
            WAHA
        </option>
        @if($includeEvolutionProvider)
        <option value="evolution" {{ $providerValue == 'evolution' ? 'selected' : '' }}>
            Evolution API
        </option>
        @endif
    </select>
    <small class="text-muted text-xs text-gray-500 dark:text-gray-400">Escolha qual provedor de WhatsApp sera usado pelo sistema.</small>
</div>

<div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="whatsapp_business">
    <div class="d-flex justify-content-between items-center mb-2">
        <h6 class="mb-0 text-sm font-semibold text-gray-900 dark:text-white">Meta (WhatsApp Business)</h6>
        <span id="meta-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
    </div>
    <div class="mb-3">
        <label>Access Token</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="META_ACCESS_TOKEN"
               value="{{ old('META_ACCESS_TOKEN', $settings['META_ACCESS_TOKEN'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>Phone Number ID</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="META_PHONE_NUMBER_ID"
               value="{{ old('META_PHONE_NUMBER_ID', $settings['META_PHONE_NUMBER_ID'] ?? '') }}">
    </div>
    <div class="mb-3">
        <label>WABA ID</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="META_WABA_ID"
               value="{{ old('META_WABA_ID', $settings['META_WABA_ID'] ?? '') }}">
        <small class="text-muted text-xs text-gray-500 dark:text-gray-400">Necessário para criar/sincronizar templates oficiais na Meta.</small>
    </div>
    <div class="d-flex flex-column sm:flex-row gap-2 mb-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 border border-gray-300 rounded-lg" id="btn-test-meta"
                data-test-url="{{ $metaTestUrl ?? '#' }}">
            <i class="fas fa-plug me-1"></i> Testar Conexao Meta
        </button>
        <button type="button" class="btn btn-outline-primary px-3 py-2 border border-blue-500 text-blue-600 rounded-lg" id="btn-toggle-meta-send">
            <i class="fas fa-paper-plane me-1"></i> Testar Envio Meta
        </button>
    </div>
    <small id="meta-test-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mb-2"></small>

    <div id="meta-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
        <div class="mb-2">
            <label for="meta-test-number" class="form-label">Numero de destino</label>
            <input type="text" id="meta-test-number" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
        </div>
        <div class="mb-2">
            <label for="meta-test-message-input" class="form-label">Mensagem</label>
            <textarea id="meta-test-message-input" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Meta - Plataforma AgeClin</textarea>
        </div>
        <div class="d-flex flex-column sm:flex-row gap-2 items-start sm:items-center">
            <button type="button" class="btn btn-success px-3 py-2 bg-green-600 text-white rounded-lg" id="btn-send-meta-test"
                    data-send-url="{{ $metaSendUrl ?? '#' }}">
                <i class="fas fa-paper-plane me-1"></i> Enviar teste
            </button>
            <span id="meta-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
        </div>
        <small id="meta-send-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
    </div>
</div>

@if($includeEvolutionProvider)
<div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="evolution">
    <div class="d-flex justify-content-between items-center mb-2">
        <h6 class="mb-0 text-sm font-semibold text-gray-900 dark:text-white">Evolution API (WhatsApp)</h6>
        <span id="evolution-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
    </div>
    <div class="mb-3">
        <label>Base URL</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="EVOLUTION_BASE_URL"
               value="{{ old('EVOLUTION_BASE_URL', $settings['EVOLUTION_BASE_URL'] ?? '') }}" placeholder="https://seu-servidor-evolution">
    </div>
    <div class="mb-3">
        <label>API Key</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="EVOLUTION_API_KEY"
               value="{{ old('EVOLUTION_API_KEY', $settings['EVOLUTION_API_KEY'] ?? '') }}" placeholder="apikey">
    </div>
    <div class="mb-3">
        <label>Instancia padrao da Platform</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="EVOLUTION_INSTANCE"
               value="{{ old('EVOLUTION_INSTANCE', $settings['EVOLUTION_INSTANCE'] ?? 'default') }}" placeholder="default">
        <small class="text-muted text-xs text-gray-500 dark:text-gray-400">
            Usada apenas nas operacoes da Platform (ex: teste de envio). Nao define a instancia dos tenants em API global.
        </small>
    </div>
    <div class="d-flex flex-column sm:flex-row gap-2 mb-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 border border-gray-300 rounded-lg" id="btn-test-evolution"
                data-test-url="{{ $evolutionTestUrl ?? '#' }}">
            <i class="fas fa-plug me-1"></i> Testar Conexao Evolution
        </button>
        <button type="button" class="btn btn-outline-primary px-3 py-2 border border-blue-500 text-blue-600 rounded-lg" id="btn-toggle-evolution-send">
            <i class="fas fa-paper-plane me-1"></i> Testar Envio Evolution
        </button>
    </div>
    <small id="evolution-test-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mb-2"></small>

    <div id="evolution-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
        <div class="mb-2">
            <label for="evolution-test-number" class="form-label">Numero de destino</label>
            <input type="text" id="evolution-test-number" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
        </div>
        <div class="mb-2">
            <label for="evolution-test-message-input" class="form-label">Mensagem</label>
            <textarea id="evolution-test-message-input" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Evolution - Plataforma AgeClin</textarea>
        </div>
        <div class="d-flex flex-column sm:flex-row gap-2 items-start sm:items-center">
            <button type="button" class="btn btn-success px-3 py-2 bg-green-600 text-white rounded-lg" id="btn-send-evolution-test"
                    data-send-url="{{ $evolutionSendUrl ?? '#' }}">
                <i class="fas fa-paper-plane me-1"></i> Enviar teste
            </button>
            <span id="evolution-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
        </div>
        <small id="evolution-send-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
    </div>
</div>
@endif

<div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="zapi">
    <div class="d-flex justify-content-between items-center mb-2">
        <h6 class="mb-0 text-sm font-semibold text-gray-900 dark:text-white">Z-API (WhatsApp)</h6>
        <span id="zapi-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
    </div>
    <div class="mb-3">
        <label>API URL</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="ZAPI_API_URL"
               value="{{ old('ZAPI_API_URL', $settings['ZAPI_API_URL'] ?? '') }}" placeholder="https://api.z-api.io">
    </div>
    <div class="mb-3">
        <label>Token</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="ZAPI_TOKEN"
               value="{{ old('ZAPI_TOKEN', $settings['ZAPI_TOKEN'] ?? '') }}" placeholder="Token da instancia">
        <small class="text-muted text-xs text-gray-500 dark:text-gray-400">Token da instancia Z-API (usado na URL).</small>
    </div>
    <div class="mb-3">
        <label>Client Token</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="ZAPI_CLIENT_TOKEN"
               value="{{ old('ZAPI_CLIENT_TOKEN', $settings['ZAPI_CLIENT_TOKEN'] ?? '') }}" placeholder="Client-Token de seguranca">
        <small class="text-muted text-xs text-gray-500 dark:text-gray-400">Client-Token de seguranca da conta (usado no header).</small>
    </div>
    <div class="mb-3">
        <label>Instance ID</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="ZAPI_INSTANCE_ID"
               value="{{ old('ZAPI_INSTANCE_ID', $settings['ZAPI_INSTANCE_ID'] ?? '') }}" placeholder="ID da instancia">
        <small class="text-muted text-xs text-gray-500 dark:text-gray-400">ID da instancia Z-API.</small>
    </div>
    <div class="d-flex flex-column sm:flex-row gap-2 mb-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 border border-gray-300 rounded-lg" id="btn-test-zapi"
                data-test-url="{{ $zapiTestUrl ?? '#' }}">
            <i class="fas fa-plug me-1"></i> Testar Conexao Z-API
        </button>
        <button type="button" class="btn btn-outline-primary px-3 py-2 border border-blue-500 text-blue-600 rounded-lg" id="btn-toggle-zapi-send">
            <i class="fas fa-paper-plane me-1"></i> Testar Envio Z-API
        </button>
    </div>
    <small id="zapi-test-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mb-2"></small>

    <div id="zapi-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
        <div class="mb-2">
            <label for="zapi-test-number" class="form-label">Numero de destino</label>
            <input type="text" id="zapi-test-number" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
        </div>
        <div class="mb-2">
            <label for="zapi-test-message-input" class="form-label">Mensagem</label>
            <textarea id="zapi-test-message-input" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio Z-API - Plataforma AgeClin</textarea>
        </div>
        <div class="d-flex flex-column sm:flex-row gap-2 items-start sm:items-center">
            <button type="button" class="btn btn-success px-3 py-2 bg-green-600 text-white rounded-lg" id="btn-send-zapi-test"
                    data-send-url="{{ $zapiSendUrl ?? '#' }}">
                <i class="fas fa-paper-plane me-1"></i> Enviar teste
            </button>
            <span id="zapi-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
        </div>
        <small id="zapi-send-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
    </div>
</div>

<div class="border rounded p-3 mb-3 whatsapp-provider-section" data-provider="waha">
    <div class="d-flex justify-content-between items-center mb-2">
        <h6 class="mb-0 text-sm font-semibold text-gray-900 dark:text-white">WAHA (WhatsApp Gateway)</h6>
        <span id="waha-test-badge" class="badge bg-secondary d-none">Aguardando teste</span>
    </div>
    <div class="mb-3">
        <label>Base URL</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="WAHA_BASE_URL"
               value="{{ old('WAHA_BASE_URL', $settings['WAHA_BASE_URL'] ?? '') }}" placeholder="https://seu-servidor-waha">
    </div>
    <div class="mb-3">
        <label>API Key</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="WAHA_API_KEY"
               value="{{ old('WAHA_API_KEY', $settings['WAHA_API_KEY'] ?? '') }}" placeholder="X-Api-Key">
    </div>
    <div class="mb-3">
        <label>Nome da Sessao</label>
        <input type="text" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" name="WAHA_SESSION"
               value="{{ old('WAHA_SESSION', $settings['WAHA_SESSION'] ?? '') }}" placeholder="default">
    </div>
    <div class="d-flex flex-column sm:flex-row gap-2 mb-2">
        <button type="button" class="btn btn-outline-secondary px-3 py-2 border border-gray-300 rounded-lg" id="btn-test-waha"
                data-test-url="{{ $wahaTestUrl ?? '#' }}">
            <i class="fas fa-plug me-1"></i> Testar Sessao WAHA
        </button>
        <button type="button" class="btn btn-outline-primary px-3 py-2 border border-blue-500 text-blue-600 rounded-lg" id="btn-toggle-waha-send">
            <i class="fas fa-paper-plane me-1"></i> Testar Envio WAHA
        </button>
    </div>
    <small id="waha-test-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mb-2"></small>

    <div id="waha-send-form" class="border rounded p-3 bg-light dark:bg-gray-700 d-none">
        <div class="mb-2">
            <label for="waha-test-number" class="form-label">Numero de destino</label>
            <input type="text" id="waha-test-number" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" placeholder="Ex: 5511999999999">
        </div>
        <div class="mb-2">
            <label for="waha-test-message-input" class="form-label">Mensagem</label>
            <textarea id="waha-test-message-input" class="form-control w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white" rows="3">Teste de envio WAHA - Plataforma AgeClin</textarea>
        </div>
        <div class="d-flex flex-column sm:flex-row gap-2 items-start sm:items-center">
            <button type="button" class="btn btn-success px-3 py-2 bg-green-600 text-white rounded-lg" id="btn-send-waha-test"
                    data-send-url="{{ $wahaSendUrl ?? '#' }}">
                <i class="fas fa-paper-plane me-1"></i> Enviar teste
            </button>
            <span id="waha-send-badge" class="badge bg-secondary d-none">Aguardando envio</span>
        </div>
        <small id="waha-send-message" class="text-muted text-xs text-gray-500 dark:text-gray-400 d-block mt-2"></small>
    </div>
</div>

