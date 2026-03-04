let chartJsPromise = null;
let evolutionChart = null;
let modeChart = null;
let byDoctorChart = null;

const REPORT_DEFINITIONS = {
    appointments: {
        columns: [
            { id: 'patient', name: 'Paciente' },
            { id: 'doctor', name: 'Medico' },
            { id: 'specialty', name: 'Especialidade' },
            { id: 'type', name: 'Tipo' },
            { id: 'date', name: 'Data' },
            { id: 'time', name: 'Hora' },
            { id: 'mode_badge', name: 'Modo', html: true },
            { id: 'status_badge', name: 'Status', html: true },
            { id: 'actions', name: 'Acoes', html: true },
        ],
        onPayload: (payload) => {
            updateAppointmentsSummary(payload?.summary || {});
            updateAppointmentsCharts(payload?.chart || {});
        },
    },
    patients: {
        columns: [
            { id: 'name', name: 'Nome' },
            { id: 'email', name: 'E-mail' },
            { id: 'phone', name: 'Telefone' },
            { id: 'appointments_count', name: 'Agendamentos' },
            { id: 'created_at', name: 'Cadastrado em' },
            { id: 'actions', name: 'Acoes', html: true },
        ],
        onPayload: (payload) => {
            updatePatientsSummary(payload?.summary || {});
        },
    },
    doctors: {
        columns: [
            { id: 'name', name: 'Nome' },
            { id: 'specialties', name: 'Especialidades' },
            { id: 'appointments_count', name: 'Agendamentos' },
            { id: 'status_badge', name: 'Status', html: true },
            { id: 'actions', name: 'Acoes', html: true },
        ],
    },
    forms: {
        columns: [
            { id: 'name', name: 'Nome' },
            { id: 'responses_count', name: 'Respostas' },
            { id: 'status_badge', name: 'Status', html: true },
            { id: 'created_at', name: 'Criado em' },
            { id: 'actions', name: 'Acoes', html: true },
        ],
    },
    notifications: {
        columns: [
            { id: 'title', name: 'Titulo' },
            { id: 'type', name: 'Tipo' },
            { id: 'status_badge', name: 'Status', html: true },
            { id: 'created_at', name: 'Criada em' },
            { id: 'actions', name: 'Acoes', html: true },
        ],
    },
    portal: {
        columns: [
            { id: 'patient', name: 'Paciente' },
            { id: 'email', name: 'E-mail' },
            { id: 'status_badge', name: 'Status', html: true },
            { id: 'created_at', name: 'Criado em' },
            { id: 'actions', name: 'Acoes', html: true },
        ],
    },
    recurring: {
        columns: [
            { id: 'doctor', name: 'Medico' },
            { id: 'patient', name: 'Paciente' },
            { id: 'appointment_type', name: 'Tipo de consulta' },
            { id: 'start_date', name: 'Data inicial' },
            { id: 'status_badge', name: 'Status', html: true },
            { id: 'created_at', name: 'Criado em' },
            { id: 'actions', name: 'Acoes', html: true },
        ],
    },
};

export function init() {
    const config = document.querySelector('[id^="reports-"][data-report-type][data-grid-url]');
    if (!config || !window.gridjs) {
        return;
    }

    const reportType = config.dataset.reportType || '';
    const definition = REPORT_DEFINITIONS[reportType];
    if (!definition) {
        return;
    }

    const target = document.getElementById('reports-grid');
    if (!target) {
        return;
    }

    const filterForm = document.getElementById('filter-form');
    const state = resolveInitialState(filterForm);
    hydrateFilterForm(filterForm, state.filters);

    let grid = null;

    const buildGridUrl = () => {
        return appendQuery(config.dataset.gridUrl || '', {
            ...state.filters,
            search: state.search,
            sort: state.sort,
            dir: state.dir,
            page: state.page,
            per_page: state.perPage,
        });
    };

    const syncBrowserUrl = () => {
        const url = new URL(window.location.href);
        const params = {
            ...state.filters,
            search: state.search,
            sort: state.sort,
            dir: state.dir,
            page: state.page,
            per_page: state.perPage,
        };

        const allowed = new Set(['page', 'per_page', 'search', 'sort', 'dir']);
        Object.keys(state.filters).forEach((key) => allowed.add(key));

        Array.from(url.searchParams.keys()).forEach((key) => {
            if (allowed.has(key)) {
                url.searchParams.delete(key);
            }
        });

        Object.entries(params).forEach(([key, value]) => {
            if (value === undefined || value === null || value === '') {
                return;
            }
            url.searchParams.set(key, String(value));
        });

        window.history.replaceState({}, '', `${url.pathname}${url.search}`);
    };

    const buildExportUrl = (format) => {
        const exportBase = format === 'pdf' ? config.dataset.exportPdfUrl : config.dataset.exportExcelUrl;

        return appendQuery(exportBase || '', {
            ...state.filters,
            search: state.search,
            sort: state.sort,
            dir: state.dir,
        });
    };

    const handlePayload = (payload) => {
        if (typeof definition.onPayload === 'function') {
            definition.onPayload(payload);
        }
    };

    const renderGrid = () => {
        if (grid) {
            grid.destroy();
            target.innerHTML = '';
        }

        const columns = definition.columns.map((column) => {
            if (!column.html) {
                return column;
            }

            return {
                ...column,
                formatter: (cell) => gridjs.html(cell ?? ''),
            };
        });

        grid = new gridjs.Grid({
            columns,
            server: {
                url: buildGridUrl(),
                method: 'GET',
                handle: async (response) => {
                    if (!response.ok) {
                        throw new Error(`Erro ao carregar dados (HTTP ${response.status})`);
                    }

                    const payload = await response.json();
                    handlePayload(payload);
                    return payload;
                },
                then: (payload) => (Array.isArray(payload?.data) ? payload.data : []),
                total: (payload) => Number(payload?.meta?.total || 0),
            },
            search: {
                enabled: true,
                keyword: state.search,
                debounceTimeout: 350,
                server: {
                    url: (_prev, keyword) => {
                        state.search = keyword || '';
                        state.page = 1;
                        syncBrowserUrl();
                        return buildGridUrl();
                    },
                },
            },
            sort: {
                multiColumn: false,
                server: {
                    url: (_prev, columnsState) => {
                        if (!Array.isArray(columnsState) || columnsState.length === 0) {
                            state.sort = '';
                            state.dir = '';
                        } else {
                            const col = columnsState[0];
                            state.sort = col.id;
                            state.dir = col.direction === 1 ? 'asc' : 'desc';
                        }

                        state.page = 1;
                        syncBrowserUrl();
                        return buildGridUrl();
                    },
                },
            },
            pagination: {
                enabled: true,
                limit: state.perPage,
                server: {
                    url: (_prev, page, limit) => {
                        state.page = Number(page) + 1;
                        state.perPage = Number(limit) || state.perPage;
                        syncBrowserUrl();
                        return buildGridUrl();
                    },
                },
            },
            language: {
                search: { placeholder: 'Pesquisar...' },
                pagination: { previous: 'Anterior', next: 'Proxima' },
                loading: 'Carregando...',
                noRecordsFound: 'Nenhum registro encontrado',
                error: 'Erro ao carregar dados',
            },
            className: {
                table: 'w-full text-left',
            },
        });

        grid.render(target);
    };

    document.addEventListener('click', (event) => {
        const exportButton = event.target.closest('[data-export-format]');
        if (exportButton) {
            event.preventDefault();
            const format = exportButton.dataset.exportFormat === 'pdf' ? 'pdf' : 'excel';
            const url = buildExportUrl(format);
            if (url) {
                window.location.assign(url);
            }
            return;
        }

        const actionButton = event.target.closest('[data-reports-action]');
        if (!actionButton) {
            return;
        }

        if (actionButton.dataset.reportsAction === 'apply-filters') {
            state.filters = readFilters(filterForm);
            state.page = 1;
            syncBrowserUrl();
            renderGrid();
            return;
        }

        if (actionButton.dataset.reportsAction === 'reset-filters') {
            if (filterForm) {
                filterForm.reset();
            }
            state.filters = {};
            state.search = '';
            state.sort = '';
            state.dir = '';
            state.page = 1;
            syncBrowserUrl();
            renderGrid();
        }
    });

    syncBrowserUrl();
    renderGrid();
}

function resolveInitialState(filterForm) {
    const params = new URLSearchParams(window.location.search);

    const filters = {};
    if (filterForm) {
        const names = new Set(
            Array.from(filterForm.elements)
                .map((element) => element?.name)
                .filter(Boolean),
        );

        names.forEach((name) => {
            const value = params.get(name);
            if (value !== null && value !== '') {
                filters[name] = value;
            }
        });
    }

    const perPageRaw = Number.parseInt(params.get('per_page') || params.get('limit') || '25', 10);
    const perPage = [10, 25, 50, 100].includes(perPageRaw) ? perPageRaw : 25;

    return {
        page: Math.max(1, Number.parseInt(params.get('page') || '1', 10) || 1),
        perPage,
        search: params.get('search') || '',
        sort: params.get('sort') || '',
        dir: params.get('dir') || '',
        filters,
    };
}

function hydrateFilterForm(filterForm, filters) {
    if (!filterForm) {
        return;
    }

    Object.entries(filters || {}).forEach(([name, value]) => {
        const field = filterForm.elements.namedItem(name);
        if (!field) {
            return;
        }

        if (Array.isArray(field)) {
            field.forEach((input) => {
                if ('value' in input) {
                    input.value = value;
                }
            });
            return;
        }

        if ('value' in field) {
            field.value = value;
        }
    });
}

function readFilters(filterForm) {
    if (!filterForm) {
        return {};
    }

    const formData = new FormData(filterForm);
    const filters = {};

    formData.forEach((value, key) => {
        const normalized = String(value || '').trim();
        if (normalized !== '') {
            filters[key] = normalized;
        }
    });

    return filters;
}

function appendQuery(url, params) {
    if (!url) {
        return '';
    }

    const targetUrl = new URL(url, window.location.origin);

    Object.entries(params || {}).forEach(([key, value]) => {
        if (!key) {
            return;
        }

        if (value === undefined || value === null || value === '') {
            targetUrl.searchParams.delete(key);
            return;
        }

        targetUrl.searchParams.set(key, String(value));
    });

    if (targetUrl.origin === window.location.origin) {
        return `${targetUrl.pathname}${targetUrl.search}`;
    }

    return targetUrl.toString();
}

function updateAppointmentsSummary(summary) {
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = String(value ?? 0);
        }
    };

    setText('summary-total', summary.total || 0);
    setText('summary-scheduled', summary.scheduled || 0);
    setText('summary-attended', summary.attended || 0);
    setText('summary-canceled', summary.canceled || 0);
    setText('summary-online', summary.online || 0);
    setText('summary-presencial', summary.presencial || 0);
}

function updatePatientsSummary(summary) {
    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = String(value ?? 0);
        }
    };

    setText('summary-total', summary.total || 0);
    setText('summary-with-appointments', summary.with_appointments || 0);
    setText('summary-new-this-month', summary.new_this_month || 0);
}

function updateHeatmap(heatmapData) {
    const container = document.getElementById('heatmap-container');
    if (!container) {
        return;
    }

    const days = ['Domingo', 'Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado'];
    const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];

    let maxValue = 0;
    Object.values(heatmapData || {}).forEach((dayMap) => {
        Object.values(dayMap || {}).forEach((value) => {
            const parsed = Number(value || 0);
            if (parsed > maxValue) {
                maxValue = parsed;
            }
        });
    });

    let html = '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs">';
    html += '<thead class="bg-gray-50 dark:bg-gray-700/50"><tr>';
    html += '<th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">Hora</th>';

    days.forEach((day) => {
        html += `<th class="px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300">${day}</th>`;
    });

    html += '</tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-700">';

    hours.forEach((hour) => {
        html += `<tr><td class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-200">${hour}h</td>`;

        days.forEach((day) => {
            const value = Number(heatmapData?.[day]?.[hour] || 0);
            const intensity = maxValue > 0 ? value / maxValue : 0;
            const background = `rgba(79, 141, 249, ${0.2 + intensity * 0.75})`;
            html += `<td class="px-3 py-2 text-center text-gray-900 dark:text-gray-100" style="background-color:${background}">${value}</td>`;
        });

        html += '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

async function updateAppointmentsCharts(chartData) {
    const Chart = await loadChartJs();
    if (!Chart) {
        return;
    }

    const evolutionCtx = document.getElementById('evolutionChart')?.getContext('2d');
    const modeCtx = document.getElementById('modeChart')?.getContext('2d');
    const byDoctorCtx = document.getElementById('byDoctorChart')?.getContext('2d');

    if (!evolutionCtx || !modeCtx || !byDoctorCtx) {
        return;
    }

    const evolutionLabels = Object.keys(chartData?.evolution || {}).sort();
    const evolutionValues = evolutionLabels.map((label) => Number(chartData.evolution[label] || 0));

    if (evolutionChart) {
        evolutionChart.destroy();
    }

    evolutionChart = new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: evolutionLabels,
            datasets: [
                {
                    label: 'Agendamentos',
                    data: evolutionValues,
                    borderColor: '#4F8DF9',
                    backgroundColor: 'rgba(79, 141, 249, 0.1)',
                    tension: 0.35,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        },
    });

    if (modeChart) {
        modeChart.destroy();
    }

    modeChart = new Chart(modeCtx, {
        type: 'pie',
        data: {
            labels: ['Online', 'Presencial'],
            datasets: [
                {
                    data: [Number(chartData?.mode?.online || 0), Number(chartData?.mode?.presencial || 0)],
                    backgroundColor: ['#f59e0b', '#64748b'],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        },
    });

    const byDoctorLabels = Object.keys(chartData?.byDoctor || {});
    const byDoctorValues = byDoctorLabels.map((label) => Number(chartData.byDoctor[label] || 0));

    if (byDoctorChart) {
        byDoctorChart.destroy();
    }

    byDoctorChart = new Chart(byDoctorCtx, {
        type: 'bar',
        data: {
            labels: byDoctorLabels,
            datasets: [
                {
                    label: 'Agendamentos',
                    data: byDoctorValues,
                    backgroundColor: '#0891b2',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
        },
    });

    updateHeatmap(chartData?.heatmap || {});
}

function loadChartJs() {
    if (window.Chart) {
        return Promise.resolve(window.Chart);
    }

    if (!chartJsPromise) {
        chartJsPromise = new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
            script.onload = () => resolve(window.Chart || null);
            script.onerror = () => resolve(null);
            document.head.appendChild(script);
        });
    }

    return chartJsPromise;
}
