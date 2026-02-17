function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

function loadChartJs() {
    if (window.Chart) {
        return Promise.resolve(window.Chart);
    }

    return import('https://cdn.jsdelivr.net/npm/chart.js')
        .then((module) => module.Chart || module.default || window.Chart)
        .catch(() => null);
}

function initFinanceDashboardCharts() {
    const monthlyCanvas = document.getElementById('monthlyIncomeChart');
    const categoryCanvas = document.getElementById('incomeByCategoryChart');

    if (!monthlyCanvas && !categoryCanvas) return;

    const monthlySeries = (() => {
        if (!monthlyCanvas?.dataset?.series) return null;
        try {
            return JSON.parse(monthlyCanvas.dataset.series);
        } catch (error) {
            return null;
        }
    })();

    const categorySeries = (() => {
        if (!categoryCanvas?.dataset?.series) return null;
        try {
            return JSON.parse(categoryCanvas.dataset.series);
        } catch (error) {
            return null;
        }
    })();

    loadChartJs().then((Chart) => {
        if (!Chart) return;

        if (monthlyCanvas && Array.isArray(monthlySeries)) {
            const ctx = monthlyCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: monthlySeries.map((item) => item.month),
                    datasets: [
                        {
                            label: 'Receitas',
                            data: monthlySeries.map((item) => item.value),
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        if (categoryCanvas && Array.isArray(categorySeries)) {
            const ctx = categoryCanvas.getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: categorySeries.map((item) => item.name),
                    datasets: [
                        {
                            data: categorySeries.map((item) => item.total),
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.8)',
                                'rgba(54, 162, 235, 0.8)',
                                'rgba(255, 206, 86, 0.8)',
                                'rgba(75, 192, 192, 0.8)',
                                'rgba(153, 102, 255, 0.8)'
                            ]
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
        }
    });
}

function bindExportButtons(form) {
    const template = form.dataset.exportUrlTemplate;
    if (!template) return;

    form.querySelectorAll('[data-export-format]').forEach((button) => {
        button.addEventListener('click', () => {
            const format = button.dataset.exportFormat;
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            window.location.href = template.replace('FORMAT', format) + '?' + params.toString();
        });
    });
}

function renderCashflow(data) {
    const tbody = document.getElementById('resultsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (data.data && data.data.length > 0) {
        data.data.forEach((item) => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${item.date}</td>
                <td><span class="badge ${item.type === 'Receita' ? 'bg-success' : 'bg-danger'}">${item.type}</span></td>
                <td>${item.category}</td>
                <td>${item.account}</td>
                <td>R$ ${item.amount}</td>
                <td>R$ ${item.balance}</td>
                <td><span class="badge ${item.status === 'paid' ? 'bg-success' : 'bg-warning'}">${item.status}</span></td>
            `;
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>';
    }

    if (data.summary) {
        const summary = document.getElementById('summary');
        if (!summary) return;
        summary.innerHTML = `
            <div class="alert alert-info">
                <strong>Resumo:</strong>
                Receitas: R$ ${parseFloat(data.summary.total_income).toFixed(2).replace('.', ',')} | 
                Despesas: R$ ${parseFloat(data.summary.total_expense).toFixed(2).replace('.', ',')} | 
                Saldo Final: R$ ${parseFloat(data.summary.final_balance).toFixed(2).replace('.', ',')}
            </div>
        `;
    }
}

function renderCharges(data) {
    const tbody = document.getElementById('resultsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (data.data && data.data.length > 0) {
        data.data.forEach((item) => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${item.patient}</td>
                <td>${item.appointment}</td>
                <td>${item.doctor}</td>
                <td>R$ ${item.amount}</td>
                <td><span class="badge ${getStatusBadge(item.status)}">${item.status}</span></td>
                <td><span class="badge bg-info">${item.origin}</span></td>
                <td>${item.due_date}</td>
            `;
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhum resultado encontrado</td></tr>';
    }

    if (data.summary) {
        const summary = document.getElementById('summary');
        if (!summary) return;
        summary.innerHTML = `
            <div class="alert alert-info">
                <strong>Resumo:</strong>
                Total: R$ ${parseFloat(data.summary.total).toFixed(2).replace('.', ',')} | 
                Pago: R$ ${parseFloat(data.summary.paid).toFixed(2).replace('.', ',')} | 
                Pendente: R$ ${parseFloat(data.summary.pending).toFixed(2).replace('.', ',')} | 
                Cancelado: R$ ${parseFloat(data.summary.cancelled).toFixed(2).replace('.', ',')}
            </div>
        `;
    }
}

function renderCommissions(data) {
    const tbody = document.getElementById('resultsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (data.data && data.data.length > 0) {
        data.data.forEach((item) => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${item.doctor}</td>
                <td>${item.appointment}</td>
                <td>R$ ${item.amount}</td>
                <td>${item.percentage}</td>
                <td><span class="badge ${item.status === 'paid' ? 'bg-success' : 'bg-warning'}">${item.status}</span></td>
                <td>${item.paid_at}</td>
            `;
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado</td></tr>';
    }

    if (data.summary) {
        const summary = document.getElementById('summary');
        if (!summary) return;
        summary.innerHTML = `
            <div class="alert alert-info">
                <strong>Resumo:</strong>
                Total: R$ ${parseFloat(data.summary.total).toFixed(2).replace('.', ',')} | 
                Pago: R$ ${parseFloat(data.summary.paid).toFixed(2).replace('.', ',')} | 
                Pendente: R$ ${parseFloat(data.summary.pending).toFixed(2).replace('.', ',')}
            </div>
        `;
    }
}

function renderPayments(data) {
    const tbody = document.getElementById('resultsBody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (data.data && data.data.length > 0) {
        data.data.forEach((item) => {
            const row = tbody.insertRow();
            row.innerHTML = `
                <td>${item.patient}</td>
                <td>R$ ${item.amount}</td>
                <td>${item.payment_method}</td>
                <td>${item.payment_date}</td>
                <td>${item.appointment}</td>
                <td>${item.doctor}</td>
            `;
        });
    } else {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum resultado encontrado</td></tr>';
    }

    if (data.summary) {
        const summary = document.getElementById('summary');
        if (!summary) return;
        summary.innerHTML = `
            <div class="alert alert-success">
                <strong>Resumo:</strong>
                Total Recebido: R$ ${parseFloat(data.summary.total).toFixed(2).replace('.', ',')} | 
                Quantidade: ${data.summary.count}
            </div>
        `;
    }
}

function renderIncomeExpense(data, chartHolder) {
    const labels = Object.keys(data.income).concat(Object.keys(data.expense));
    const uniqueLabels = [...new Set(labels)].sort();
    const incomeData = uniqueLabels.map((label) => data.income[label] || 0);
    const expenseData = uniqueLabels.map((label) => data.expense[label] || 0);

    loadChartJs().then((Chart) => {
        if (!Chart) return;
        if (chartHolder.chart) {
            chartHolder.chart.destroy();
        }
        const ctx = document.getElementById('chart')?.getContext('2d');
        if (!ctx) return;
        chartHolder.chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: uniqueLabels,
                datasets: [
                    {
                        label: 'Receitas',
                        data: incomeData,
                        backgroundColor: 'rgba(75, 192, 192, 0.8)'
                    },
                    {
                        label: 'Despesas',
                        data: expenseData,
                        backgroundColor: 'rgba(255, 99, 132, 0.8)'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });

    if (data.summary) {
        const summary = document.getElementById('summary');
        if (!summary) return;
        summary.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>Total Receitas</h5>
                            <h3>R$ ${parseFloat(data.summary.total_income).toFixed(2).replace('.', ',')}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5>Total Despesas</h5>
                            <h3>R$ ${parseFloat(data.summary.total_expense).toFixed(2).replace('.', ',')}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card ${data.summary.net_result >= 0 ? 'bg-primary' : 'bg-warning'} text-white">
                        <div class="card-body">
                            <h5>Resultado Líquido</h5>
                            <h3>R$ ${parseFloat(data.summary.net_result).toFixed(2).replace('.', ',')}</h3>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
}

function getStatusBadge(status) {
    const badges = {
        paid: 'bg-success',
        pending: 'bg-warning',
        cancelled: 'bg-secondary'
    };
    return badges[status] || 'bg-secondary';
}

function initFinanceReportForm() {
    const form = document.getElementById('filterForm');
    if (!form) return;

    const report = form.dataset.report;
    const fetchUrl = form.dataset.fetchUrl;
    if (!report || !fetchUrl) return;

    const chartHolder = {};

    const loadData = () => {
        const formData = new FormData(form);

        fetch(fetchUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': getCsrfToken()
            }
        })
            .then((response) => response.json())
            .then((data) => {
                if (report === 'cashflow') {
                    renderCashflow(data);
                } else if (report === 'charges') {
                    renderCharges(data);
                } else if (report === 'commissions') {
                    renderCommissions(data);
                } else if (report === 'payments') {
                    renderPayments(data);
                } else if (report === 'income-expense') {
                    renderIncomeExpense(data, chartHolder);
                }
            })
            .catch((error) => {
                // eslint-disable-next-line no-console
                console.error('Erro:', error);
                if (typeof showAlert === 'function') {
                    showAlert({ type: 'error', title: 'Erro', message: 'Erro ao carregar dados' });
                }
            });
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        loadData();
    });

    bindExportButtons(form);
}

export function init() {
    initFinanceDashboardCharts();
    initFinanceReportForm();
}
