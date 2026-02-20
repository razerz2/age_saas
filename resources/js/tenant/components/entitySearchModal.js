function buildModalMarkup() {
	return `
		<div id="entitySearchModal" class="entity-search-modal hidden" data-entity-search-modal>
			<div class="entity-search-modal__backdrop" data-entity-search-backdrop></div>
			<div class="entity-search-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="entity-search-modal-title">
				<div class="entity-search-modal__header">
					<h3 id="entity-search-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white" data-entity-search-title>Buscar</h3>
					<button type="button" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 js-close-entity-search-modal" aria-label="Fechar modal de busca">✕</button>
				</div>
				<div class="entity-search-modal__body">
					<input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white" placeholder="Digite para buscar..." data-entity-search-input>
					<div class="entity-search-modal__results-wrap border border-gray-200 dark:border-gray-700 rounded-md mt-3">
						<div class="p-3 text-sm text-gray-500 dark:text-gray-400" data-entity-search-empty>Digite para buscar.</div>
						<div class="hidden p-3 text-sm text-gray-500 dark:text-gray-400" data-entity-search-loading>Buscando...</div>
						<ul class="hidden" data-entity-search-results></ul>
					</div>
				</div>
				<div class="entity-search-modal__footer">
					<button type="button" class="btn btn-outline js-cancel-entity-search">Cancelar</button>
					<button type="button" class="btn btn-primary js-confirm-entity-search" data-entity-search-confirm disabled>Selecionar</button>
				</div>
			</div>
		</div>
	`;
}

function ensureModal() {
	let modal = document.querySelector('[data-entity-search-modal]');
	if (modal) return modal;

	document.body.insertAdjacentHTML('beforeend', buildModalMarkup());
	modal = document.querySelector('[data-entity-search-modal]');
	return modal;
}

function normalizeItems(payload) {
	if (Array.isArray(payload)) return payload;
	if (Array.isArray(payload?.data)) return payload.data;
	return [];
}

export function initEntitySearchModal(config = {}) {
	const triggerSelector = config.triggerSelector || '.js-open-entity-search';
	const triggers = Array.from(document.querySelectorAll(triggerSelector));
	if (triggers.length === 0) return;

	const modal = ensureModal();
	if (!modal || modal.dataset.entitySearchBound === '1') return;

	const searchInput = modal.querySelector('[data-entity-search-input]');
	const listEl = modal.querySelector('[data-entity-search-results]');
	const emptyEl = modal.querySelector('[data-entity-search-empty]');
	const loadingEl = modal.querySelector('[data-entity-search-loading]');
	const titleEl = modal.querySelector('[data-entity-search-title]');
	const confirmButton = modal.querySelector('[data-entity-search-confirm]');
	const backdrop = modal.querySelector('[data-entity-search-backdrop]');
	const cancelButton = modal.querySelector('.js-cancel-entity-search');

	let debounceTimer = null;
	let currentConfig = null;
	let selectedItem = null;
	let selectedButton = null;

	const applySelection = (button, item) => {
		if (selectedButton) {
			selectedButton.classList.remove('entity-search-modal__result--selected');
			selectedButton.setAttribute('aria-selected', 'false');
		}

		selectedButton = button;
		selectedItem = item;

		if (selectedButton) {
			selectedButton.classList.add('entity-search-modal__result--selected');
			selectedButton.setAttribute('aria-selected', 'true');
		}

		if (confirmButton) {
			confirmButton.disabled = !selectedItem;
		}
	};

	const resetSelection = () => applySelection(null, null);

	const renderEmpty = (message) => {
		listEl.classList.add('hidden');
		listEl.innerHTML = '';
		loadingEl.classList.add('hidden');
		emptyEl.classList.remove('hidden');
		emptyEl.textContent = message;
		resetSelection();
	};

	const closeModal = () => {
		modal.classList.add('hidden');
		searchInput.value = '';
		currentConfig = null;
		resetSelection();
		renderEmpty('Digite para buscar.');
	};

	const commitSelection = () => {
		if (!currentConfig || !selectedItem) return;

		const hiddenInput = document.getElementById(currentConfig.hiddenInputId);
		const displayInput = document.getElementById(currentConfig.displayInputId);

		if (hiddenInput) {
			hiddenInput.value = selectedItem.id || '';
			hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
			if (hiddenInput.id === 'doctor_id') {
				hiddenInput.dataset.selectedName = selectedItem.name || '';
				hiddenInput.dispatchEvent(new CustomEvent('doctor:selected', { bubbles: true }));
			}
		}

		if (displayInput) {
			displayInput.value = selectedItem.name || '';
		}

		if (typeof config.onSelect === 'function') {
			config.onSelect(selectedItem, currentConfig);
		}

		closeModal();
	};

	const renderResults = (items) => {
		listEl.innerHTML = '';
		if (!Array.isArray(items) || items.length === 0) {
			renderEmpty('Nenhum resultado encontrado.');
			return;
		}

		resetSelection();
		items.forEach((item) => {
			const li = document.createElement('li');
			const resultButton = document.createElement('button');
			resultButton.type = 'button';
			resultButton.className = 'entity-search-modal__result w-full text-left px-3 py-2';
			resultButton.setAttribute('aria-selected', 'false');
			resultButton.innerHTML = `<span class="block text-sm font-medium text-gray-900 dark:text-white">${item.name || ''}</span><span class="block text-xs text-gray-500 dark:text-gray-400">${item.secondary || ''}</span>`;

			resultButton.addEventListener('click', () => applySelection(resultButton, item));
			resultButton.addEventListener('dblclick', () => {
				applySelection(resultButton, item);
				commitSelection();
			});
			resultButton.addEventListener('keydown', (event) => {
				if (event.key === 'Enter') {
					event.preventDefault();
					applySelection(resultButton, item);
					commitSelection();
				}
			});

			li.appendChild(resultButton);
			listEl.appendChild(li);
		});

		emptyEl.classList.add('hidden');
		loadingEl.classList.add('hidden');
		listEl.classList.remove('hidden');
	};

	const fetchResults = (query) => {
		if (!currentConfig?.searchUrl) return;

		loadingEl.classList.remove('hidden');
		emptyEl.classList.add('hidden');
		listEl.classList.add('hidden');
		resetSelection();

		fetch(`${currentConfig.searchUrl}?q=${encodeURIComponent(query)}&limit=10`)
			.then((response) => response.json())
			.then((payload) => renderResults(normalizeItems(payload)))
			.catch(() => renderEmpty('Erro ao buscar resultados.'));
	};

	searchInput.addEventListener('input', () => {
		const query = searchInput.value.trim();
		if (debounceTimer) clearTimeout(debounceTimer);
		debounceTimer = setTimeout(() => fetchResults(query), 250);
	});

	triggers.forEach((button) => {
		button.addEventListener('click', () => {
			currentConfig = {
				searchUrl: button.dataset.searchUrl,
				hiddenInputId: button.dataset.hiddenInputId,
				displayInputId: button.dataset.displayInputId,
				entityType: button.dataset.entityType,
			};

			titleEl.textContent = button.dataset.modalTitle || 'Buscar';
			renderEmpty('Digite para buscar.');
			modal.classList.remove('hidden');
			if (confirmButton) confirmButton.disabled = true;
			searchInput.focus();
		});
	});

	if (confirmButton) confirmButton.addEventListener('click', commitSelection);

	modal.querySelectorAll('.js-close-entity-search-modal').forEach((button) => {
		button.addEventListener('click', closeModal);
	});

	if (cancelButton) cancelButton.addEventListener('click', closeModal);
	if (backdrop) backdrop.addEventListener('click', closeModal);

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
			closeModal();
			return;
		}

		if (event.key === 'Enter' && !modal.classList.contains('hidden') && document.activeElement === searchInput && selectedItem) {
			event.preventDefault();
			commitSelection();
		}
	});

	modal.dataset.entitySearchBound = '1';
}
