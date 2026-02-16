<!-- Aba Clínica -->
<div class="space-y-8">
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Informações da Clínica</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Visualize e edite as informações básicas de cadastro da sua clínica.
        </p>
    </div>

    <form method="POST" action="{{ workspace_route('tenant.settings.update.registration') }}">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Razão Social <span class="text-red-500">*</span>
                </label>
                <input type="text" name="legal_name" value="{{ old('legal_name', $currentTenant->legal_name) }}" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" 
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nome Fantasia
                </label>
                <input type="text" name="trade_name" value="{{ old('trade_name', $currentTenant->trade_name) }}" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    E-mail de Contato <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="{{ old('email', $currentTenant->email) }}" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" 
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Telefone
                </label>
                <input type="text" name="phone" value="{{ old('phone', $currentTenant->phone) }}" 
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
            </div>
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 pt-8 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Endereço</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Logradouro <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="endereco" name="endereco" value="{{ old('endereco', $localizacao->endereco ?? '') }}" 
                           placeholder="Rua, Av..." required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Número <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="n_endereco" value="{{ old('n_endereco', $localizacao->n_endereco ?? '') }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Complemento
                    </label>
                    <input type="text" name="complemento" value="{{ old('complemento', $localizacao->complemento ?? '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Bairro <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="bairro" name="bairro" value="{{ old('bairro', $localizacao->bairro ?? '') }}" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        CEP <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="cep" name="cep" value="{{ old('cep', $localizacao->cep ?? '') }}" 
                           placeholder="00000-000" required
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Estado <span class="text-red-500">*</span>
                    </label>
                    <select id="estado_id" name="estado_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Selecione o estado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Cidade <span class="text-red-500">*</span>
                    </label>
                    <select id="cidade_id" name="cidade_id" required
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Selecione o estado primeiro</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn-patient-primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V2"></path>
                </svg>
                Salvar Alterações
            </button>
        </div>
    </form>
</div>
