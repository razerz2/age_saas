let chartJsPromise = null;

export function init() {
    if (document.getElementById('reports-appointments-config')) {
        initAppointmentsReport();
        return;
    }

    if (document.getElementById('reports-patients-config')) {
        initPatientsReport();
        return;
    }

    const genericConfig = document.querySelector('[id^=\"reports-\"][data-report-type]');
    if (genericConfig) {
        initGenericReport(genericConfig);
    }
}

function initAppointmentsReport() {
    const config = document.getElementById('reports-appointments-config');
    const $ = window.jQuery;
    if (!config || !$ || !$.fn || !$.fn.DataTable) {
        return;
    }

    const dataUrl = config.dataset.dataUrl || '';
    const exportUrls = {
        excel: config.dataset.exportExcelUrl || '',
        pdf: config.dataset.exportPdfUrl || '',
        csv: config.dataset.exportCsvUrl || '',
    };

    let evolutionChart;
    let modeChart;
    let byDoctorChart;
    const table = $('#reports-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json' },
        order: [[4, 'desc']],
        pageLength: 25,
        data: [],
        processing: false,
        serverSide: false,
        searching: true,
        ordering: true,
        info: true,
        paging: true,
        autoWidth: false,
        deferRender: true,
    });

    const getCsrf = () => document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';

    const loadData = () => {
        if (!dataUrl) {
            return;
        }
        if (table) {
            table.processing(true);
        }

        $.ajax({
            url: dataUrl,
            method: 'POST',
            data: $('#filter-form').serialize(),
            headers: { 'X-CSRF-TOKEN': getCsrf() },
            success: (response) => {
                if (table) {
                    table.processing(false);
                }
                updateSummary(response.summary || {});
                updateCharts(response.chart || {});
                updateTable(response.table || []);
            },
            error: (xhr, status, error) => {
                if (table) {
                    table.processing(false);
                }
                // eslint-disable-next-line no-console
                console.error('Erro ao carregar dados:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error,
                });
                showAlert({
                    type: 'error',
                    title: 'Erro',
                    message: 'Erro ao carregar dados do relatório. Verifique o console para mais detalhes.',
                });
            },
        });
    };

    const updateSummary = (summary) => {
        $('#summary-total').text(summary.total || 0);
        $('#summary-scheduled').text(summary.scheduled || 0);
        $('#summary-attended').text(summary.attended || 0);
        $('#summary-canceled').text(summary.canceled || 0);
        $('#summary-online').text(summary.online || 0);
        $('#summary-presencial').text(summary.presencial || 0);
    };

    const updateHeatmap = (heatmapData) => {
        const container = document.getElementById('heatmap-container');
        if (!container) return;
        container.innerHTML = '';

        const daysOfWeek = ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
        const hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18];
        let maxValue = 0;

        Object.values(heatmapData).forEach((day) => {
            Object.values(day || {}).forEach((val) => {
                if (val > maxValue) maxValue = val;
            });
        });

        let html =
            '<table class=\"min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-xs\">' +
            '<thead class=\"bg-gray-50 dark:bg-gray-700/50\"><tr>' +
            '<th class=\"px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300\">Hora</th>';
        daysOfWeek.forEach((day) => {
            html += `<th class=\"px-3 py-2 text-left font-semibold text-gray-600 dark:text-gray-300\">${day}</th>`;
        });
        html += '</tr></thead><tbody class=\"divide-y divide-gray-100 dark:divide-gray-700\">';

        hours.forEach((hour) => {
            html += `<tr><td class=\"px-3 py-2 font-semibold text-gray-700 dark:text-gray-200\">${hour}h</td>`;
            daysOfWeek.forEach((day) => {
                const value = heatmapData[day] && heatmapData[day][hour] ? heatmapData[day][hour] : 0;
                const intensity = maxValue > 0 ? Math.round((value / maxValue) * 100) : 0;
                const bgColor = `rgba(79, 141, 249, ${0.3 + (intensity / 100) * 0.7})`;
                html += `<td class=\"px-3 py-2 text-center text-gray-900 dark:text-gray-100\" style=\"background-color: ${bgColor};\">${value}</td>`;
            });
            html += '</tr>';
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    };

    const updateCharts = async (chartData) => {
        const Chart = await loadChartJs();
        if (!Chart) {
            return;
        }

        const evolutionCtx = document.getElementById('evolutionChart')?.getContext('2d');
        const modeCtx = document.getElementById('modeChart')?.getContext('2d');
        const byDoctorCtx = document.getElementById('byDoctorChart')?.getContext('2d');
        if (!evolutionCtx || !modeCtx || !byDoctorCtx) return;

        const evolutionLabels = Object.keys(chartData.evolution || {}).sort();
        const evolutionValues = evolutionLabels.map((date) => chartData.evolution[date] || 0);
        if (evolutionChart) evolutionChart.destroy();
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
                        tension: 0.4,
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

        if (modeChart) modeChart.destroy();
        modeChart = new Chart(modeCtx, {
            type: 'pie',
            data: {
                labels: ['Online', 'Presencial'],
                datasets: [
                    {
                        data: [chartData.mode?.online || 0, chartData.mode?.presencial || 0],
                        backgroundColor: ['#f59e0b', '#64748b'],
                    },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false },
        });

        const doctorLabels = Object.keys(chartData.byDoctor || {});
        const doctorValues = doctorLabels.map((doctor) => chartData.byDoctor[doctor] || 0);
        if (byDoctorChart) byDoctorChart.destroy();
        byDoctorChart = new Chart(byDoctorCtx, {
            type: 'bar',
            data: {
                labels: doctorLabels,
                datasets: [
                    {
                        label: 'Agendamentos',
                        data: doctorValues,
                        backgroundColor: '#0891b2',
                    },
                ],
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } },
        });

        updateHeatmap(chartData.heatmap || {});
    };

    const updateTable = (tableData) => {
        if (!table) return;
        table.clear();
        if (tableData && tableData.length > 0) {
            tableData.forEach((row) => {
                const modeBadge =
                    row.mode === 'online'
                        ? '<span class=\"inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/30 dark:text-amber-300\">Online</span>'
                        : '<span class=\"inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-900/30 dark:text-slate-300\">Presencial</span>';
                const statusBadge = `<span class=\"inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300\">${row.status_translated || row.status || 'N/A'}</span>`;
                table.row.add([
                    row.patient || 'N/A',
                    row.doctor || 'N/A',
                    row.specialty || 'N/A',
                    row.type || 'N/A',
                    row.date || 'N/A',
                    row.time || 'N/A',
                    modeBadge,
                    statusBadge,
                ]);
            });
        }
        table.draw();
    };

    document.addEventListener('click', (event) => {
        const exportBtn = event.target.closest('[data-export-format]');
        if (exportBtn) {
            const format = exportBtn.dataset.exportFormat;
            const url = exportUrls[format] || exportUrls.excel;
            const query = $('#filter-form').serialize();
            if (url) window.open(`${url}?${query}`, '_blank');
        }

        const actionBtn = event.target.closest('[data-reports-action]');
        if (!actionBtn) return;
        if (actionBtn.dataset.reportsAction === 'apply-filters') {
            loadData();
        } else if (actionBtn.dataset.reportsAction === 'reset-filters') {
            const form = document.getElementById('filter-form');
            form?.reset();
            loadData();
        }
    });

    $('#filter-form').on('change', 'select, input', () => {
        loadData();
    });

    loadData();
}

function initPatientsReport() {
    const config = document.getElementById('reports-patients-config');
    const $ = window.jQuery;
    if (!config || !$ || !$.fn || !$.fn.DataTable) {
        return;
    }

    const dataUrl = config.dataset.dataUrl || '';
    const table = $('#reports-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json' },
    });

    const getCsrf = () => document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';

    const loadData = () => {
        if (!dataUrl) return;
        $.ajax({
            url: dataUrl,
            method: 'POST',
            data: $('#filter-form').serialize(),
            headers: { 'X-CSRF-TOKEN': getCsrf() },
            success: (response) => {
                $('#summary-total').text(response.summary?.total || 0);
                $('#summary-with-appointments').text(response.summary?.with_appointments || 0);
                $('#summary-new-this-month').text(response.summary?.new_this_month || 0);
                table.clear();
                (response.table || []).forEach((row) => {
                    table.row.add([row.name, row.email, row.phone, row.appointments_count, row.created_at]);
                });
                table.draw();
            },
        });
    };

    document.addEventListener('click', (event) => {
        const actionBtn = event.target.closest('[data-reports-action]');
        if (!actionBtn) return;
        if (actionBtn.dataset.reportsAction === 'apply-filters') {
            loadData();
        }
    });

    loadData();
}

function initGenericReport(config) {
    const $ = window.jQuery;
    if (!config || !$ || !$.fn || !$.fn.DataTable) {
        return;
    }

    const dataUrl = config.dataset.dataUrl || '';
    const reportType = config.dataset.reportType || '';
    const getCsrf = () => document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || '';

    const columnsByType = {
        doctors: [
            { data: 'name' },
            { data: 'specialties' },
            { data: 'appointments_count' },
        ],
        forms: [{ data: 'name' }, { data: 'responses_count' }, { data: 'created_at' }],
        notifications: [{ data: 'title' }, { data: 'type' }, { data: 'read_at' }, { data: 'created_at' }],
        portal: [
            { data: 'patient' },
            { data: 'email' },
            {
                data: 'is_active',
                render: (data) =>
                    data
                        ? '<span class=\"inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300\">Ativo</span>'
                        : '<span class=\"inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-900/30 dark:text-slate-300\">Inativo</span>',
            },
            { data: 'created_at' },
        ],
        recurring: [
            { data: 'doctor' },
            { data: 'created_at' },
            {
                data: 'is_active',
                render: (data) =>
                    data
                        ? '<span class=\"inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300\">Ativo</span>'
                        : '<span class=\"inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800 dark:bg-slate-900/30 dark:text-slate-300\">Inativo</span>',
            },
        ],
    };

    $('#reports-table').DataTable({
        language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json' },
        ajax: {
            url: dataUrl,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': getCsrf() },
            dataSrc: 'table',
        },
        columns: columnsByType[reportType] || [],
    });
}

function loadChartJs() {
    if (window.Chart) {
        return Promise.resolve(window.Chart);
    }
    if (!chartJsPromise) {
        chartJsPromise = new Promise((resolve) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js';
            script.onload = () => resolve(window.Chart);
            script.onerror = () => resolve(null);
            document.head.appendChild(script);
        });
    }
    return chartJsPromise;
}
