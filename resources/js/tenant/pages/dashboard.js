export function init() {
    const canvas = document.getElementById('appointmentsChart');
    if (!canvas) {
        return;
    }

    const chartData = (() => {
        try {
            const raw = canvas.dataset.chartPoints;
            return raw ? JSON.parse(raw) : null;
        } catch (error) {
            return null;
        }
    })();

    if (!Array.isArray(chartData)) {
        return;
    }

    const ctx = canvas.getContext('2d');

    const loadChart = (Chart) =>
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map((item) => item.short),
                datasets: [
                    {
                        label: 'Agendamentos',
                        data: chartData.map((item) => item.total),
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#3B82F6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#3B82F6',
                        borderWidth: 1,
                        displayColors: false,
                        callbacks: {
                            title(context) {
                                const index = context[0].dataIndex;
                                return chartData[index].month;
                            },
                            label(context) {
                                return `Agendamentos: ${context.parsed.y}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 12,
                            },
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(156, 163, 175, 0.1)',
                            drawBorder: false,
                        },
                        ticks: {
                            color: '#6B7280',
                            font: {
                                size: 12,
                            },
                            precision: 0,
                        },
                    },
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            },
        });

    if (window.Chart) {
        loadChart(window.Chart);
        return;
    }

    import('https://cdn.jsdelivr.net/npm/chart.js')
        .then((module) => {
            const Chart = module.Chart || module.default || window.Chart;
            if (Chart) {
                loadChart(Chart);
            }
        })
        .catch(() => {
            // ignore chart load failures
        });
}
