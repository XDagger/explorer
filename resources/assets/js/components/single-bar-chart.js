import { Bar } from 'vue-chartjs'
import chartColors from '../config/chart-colors'

export default {
	extends: Bar,

	props: ['chartName', 'labels', 'chartData', 'color', 'integers'],

	mounted() {
		let chartColor = this.color ? chartColors[this.color] : chartColors.blue

		let options = {
			responsive: true,
			maintainAspectRatio: false,
			title: {
				display: false,
			},
			tooltips: {
				mode: 'index',
				intersect: false,
			},
			hover: {
				mode: 'nearest',
				intersect: true
			},
			scales: {
				xAxes: [{
					display: true,
					scaleLabel: {
						display: false
					}
				}],
				yAxes: [{
					display: true,
					scaleLabel: {
						display: false
					},
					ticks: {
						beginAtZero: true
					}
				}]
			}
		}

		if (this.integers) {
			options.tooltips.callbacks = {
				label: function (tooltipItem, data) {
					var label = data.datasets[tooltipItem.datasetIndex].label || '';

					if (label) {
						label += ': ';
					}

					label += tooltipItem.yLabel.toFixed(9);

					return label;
				}
			}
		}

		this.renderChart({
			labels: this.labels,
			datasets: [
				{
					label: this.chartName,
					fill: false,
					lineTension: 0,
					backgroundColor: chartColor,
					borderColor: chartColor,
					data: this.chartData,
				}
			]
		}, options)
	}
}
