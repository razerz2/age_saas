export function init() {
	// Carrega Chart.js via import dinâmico do CDN já presente no layout
	// (script global <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>).
	// Aqui apenas inicializamos o gráfico usando os dados expostos no HTML.

	const canvas = document.getElementById('appointmentsChart');
	if (!canvas || !window.Chart) {
		return;
	}

	const ctx = canvas.getContext('2d');

	// Os dados brutos continuam sendo renderizados pelo Blade em um atributo data-*
	// ou variável global. Para não mexer no backend agora, vamos ler diretamente
	// de uma variável global gerada inline se existir.
	//
	// Exemplo esperado (será mantido pela view até migrarmos totalmente):
	//   window.dashboardChartData = [...];

	const chartData = window.dashboardChartData;
	if (!Array.isArray(chartData)) {
		return;
	}

	new window.Chart(ctx, {
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
}
